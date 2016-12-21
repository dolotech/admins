/**********************************************************
 * Author : Michael
 * Email : dolotech@163.com
 * Last modified : 2016-06-11 17:14
 * Filename : imageserver.go
 * Description : 玩家头像服务器
 * *******************************************************/
package imageserver

import (
	"io"
	"mime/multipart"
	"net/http"
	"os"
	"runtime/debug"
	"strconv"

	"github.com/golang/glog"
	"github.com/gorilla/mux"
)

type Imageserver struct {
	path   string
	router *mux.Router
	port   int
}

func NewServer(path string, port int) *Imageserver {
	return &Imageserver{path: path, port: port, router: mux.NewRouter()}
}
func (this *Imageserver) Run() {
	this.router.HandleFunc("/", this.homeHandler).Methods("GET")
	//	this.router.HandleFunc("/", this.UploadHandler).Methods("POST")
	this.router.HandleFunc("/{imgid}", this.downloadHandler).Methods("GET")
	err := http.ListenAndServe("0.0.0.0:"+strconv.Itoa(this.port), this.router)
	if err != nil {
		glog.Fatal("ListenAndServe error: ", err)
	}
}
func (this *Imageserver) HandleFunc(path string, f func(http.ResponseWriter,
	*http.Request)) *mux.Route {
	return this.router.HandleFunc(path, f)
}
func SaveImage(path string, file multipart.File) (string, error) {
	defer func() {
		if err := recover(); err != nil {
			glog.Errorln(string(debug.Stack()))
		}
	}()

	//检测文件类型
	buff := make([]byte, 512)
	_, err := file.Read(buff)
	if err != nil {
		glog.Infoln(err)
		return "", err
	}
	filetype := http.DetectContentType(buff)
	glog.Infoln(filetype)
	//	if filetype != "image/jpeg" {
	//		return
	//	}
	//回绕文件指针
	glog.Infoln(filetype)
	if _, err = file.Seek(0, 0); err != nil {
		glog.Infoln(err)
	}
	//随机生成一个不存在的fileid
	var imgid string
	for {
		imgid = makeImageID()
		glog.Infoln(imgid)
		if !fileExist(imageID2Path(path, imgid)) {
			break
		}
	}
	//提前创建整棵存储树
	if err = buildTree(path, imgid); err != nil {
		glog.Infoln(err)
	}
	glog.Infoln((imgid))
	f, err := os.OpenFile(imageID2Path(path, imgid), os.O_WRONLY|os.O_CREATE, 0666)
	glog.Infoln(err)
	if err != nil {
		glog.Infoln(err)
		return "", err
	}
	defer f.Close()
	io.Copy(f, file)

	return imgid, nil
}

func (this *Imageserver) downloadHandler(w http.ResponseWriter, r *http.Request) {
	defer func() {
		if err := recover(); err != nil {
			glog.Errorln(string(debug.Stack()))
		}
	}()

	vars := mux.Vars(r)
	imageid, ok := vars["imgid"]
	if !ok {
		w.Write([]byte("Error:ImageID incorrect."))
		return

	}
	if len(imageid) != 11 {
		w.Write([]byte("Error:ImageID incorrect."))
		return
	}
	imgpath := imageID2Path(this.path, imageid)
	if !fileExist(imgpath) {
		w.Write([]byte("Error:Image Not Found."))
		return
	}
	http.ServeFile(w, r, imgpath)
}

func (this *Imageserver) homeHandler(w http.ResponseWriter, r *http.Request) {
	w.Write([]byte(`<html> <head></head> <body> <form action="/" method="post" enctype="multipart/form-data"> 用户名 ：<input type="text" name="userid" value="" />密码 ：<input type="password" name="password" value="" /><input type="file" name="uploadfile" value="" /> <input type="submit" name="submit" /> </form> </body> </html>`))
}
