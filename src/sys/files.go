/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-03-18 10:16
 * Filename      : files.go
 * Description   :
 * *******************************************************/
package sys

import (
	"basic/utils"
	"fmt"
	"io"
	"net/http"
	"os"
	"os/exec"
	"path/filepath"
	"strconv"

	"github.com/gin-gonic/gin"
)

var storage = "./AmazeUI/assets/files" //上传文件目录
var currentDirectory string

var Files files = files{}

type DeleteFiles struct {
	FileName []string `json:"filename" binding:"required"`
}

type ListFiles struct {
	Name string `json:"name"`
	Size string `json:"size"`
}

type files struct{}

func init() {
	//读取当前目录
	tempFile, _ := exec.LookPath(os.Args[0])
	currentDirectory = filepath.Dir(tempFile)
}

// IndexDown
func (f *files) IndexDown(c *gin.Context) {
	c.HTML(http.StatusOK, "index-down.html", gin.H{
		"file":  "/file",
		"files": "/assets/files/",
	})
}

// IndexUp
func (f *files) IndexUp(c *gin.Context) {
	c.HTML(http.StatusOK, "index-up.html", gin.H{
		"file":  "/file",
		"files": "/assets/files/",
	})
}

func (f *files) Delete(c *gin.Context) {
	var fl DeleteFiles
	c.Bind(&fl)
	for _, file := range fl.FileName {
		err := os.Remove(storage + "/" + file) //删除文件
		if err != nil {
			fmt.Println(utils.DateStr(), file, "失败删除文件", err)
		}
	}
	c.String(http.StatusOK, "删除文件结束")
}

func (f *files) List(c *gin.Context) {
	lm := make([]ListFiles, 0)
	//遍历目录，读出文件名、大小
	filepath.Walk(storage, func(path string, fi os.FileInfo, err error) error {
		if nil == fi {
			return err
		}
		if fi.IsDir() {
			return nil
		}
		//	fmt.Println(fi.Name(), fi.Size()/1024)
		var m ListFiles
		m.Name = fi.Name()
		m.Size = strconv.FormatInt(fi.Size()/1024, 10)
		lm = append(lm, m)
		return nil
	})
	//返回目录json数据
	c.JSON(http.StatusOK, lm)
}

func (f *files) Upload(c *gin.Context) {
	c.Request.ParseMultipartForm(32 << 20) //在使用r.MultipartForm前必须先调用ParseMultipartForm方法，参数为最大缓存
	if c.Request.MultipartForm != nil && c.Request.MultipartForm.File != nil {
		os.Chdir(storage)                               //进入存储目录
		defer os.Chdir(currentDirectory)                //退出存储目录
		fhs := c.Request.MultipartForm.File["userfile"] //获取所有上传文件信息
		num := len(fhs)
		fmt.Printf("总文件数：%d 个文件", num)
		//循环对每个文件进行处理
		for n, fheader := range fhs {
			//设置文件名
			//newFileName := strconv.FormatInt(time.Now().UnixNano(), 10) + filepath.Ext(fheader.Filename) //十进制
			newFileName := fheader.Filename
			//打开上传文件
			uploadFile, err := fheader.Open()
			if err != nil {
				fmt.Println(err)
				c.String(http.StatusBadRequest, "上传失败:", err.Error())
				return
			}
			defer uploadFile.Close()
			//保存文件
			saveFile, err := os.Create(newFileName)
			if err != nil {
				fmt.Println(err)
				c.String(http.StatusBadRequest, "上传失败:", err.Error())
				return
			}
			defer saveFile.Close()
			io.Copy(saveFile, uploadFile)

			//获取文件状态信息
			fileStat, _ := saveFile.Stat()
			//打印接收信息
			fmt.Printf("%s  NO.: %d  Size: %d KB  Name：%s\n", utils.DateStr(), n, fileStat.Size()/1024, newFileName)
		}
		c.String(http.StatusOK, "上传成功")
	}
}
