/**********************************************************
 * Author : Michael
 * Email : dolotech@163.com
 * Last modified : 2016-06-11 17:14
 * Filename : imageserver.go
 * Description : 玩家头像服务器
 * *******************************************************/
package imageserver

import (
	"basic/ssdb/gossdb"
	"data"
	"io"
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
	return &Imageserver{path: path, port: port}
}
func (this *Imageserver) Run() {
	this.router = mux.NewRouter()
	this.router.HandleFunc("/", this.homeHandler).Methods("GET")
	this.router.HandleFunc("/", this.uploadHandler).Methods("POST")
	this.router.HandleFunc("/{imgid}", this.downloadHandler).Methods("GET")
	glog.Infoln("头像服务器监听端口: ", this.port)
	err := http.ListenAndServe("0.0.0.0:"+strconv.Itoa(this.port), this.router)
	if err != nil {
		glog.Fatal("ListenAndServe error: ", err)
	}
}
func (this *Imageserver) uploadHandler(w http.ResponseWriter, r *http.Request) {
	defer func() {
		if err := recover(); err != nil {
			glog.Errorln(string(debug.Stack()))
		}
	}()
	r.ParseMultipartForm(32 << 20)
	for _, v := range r.Form {
		glog.Infoln(v)
	}
	//上传参数为uploadfile

	userid := r.FormValue("userid")
	glog.Infoln(userid)
	if userid == "" {
		w.Write([]byte("Error:userid empty."))
		return

	}
	password := r.FormValue("password")
	glog.Infoln(password)
	if password == "" {
		w.Write([]byte("Error:password empty."))
		return
	}
	user := &data.User{Userid: userid}
	if !user.PWDIsOK(password) {
		w.Write([]byte("Error:password or userid error."))
		return
	}
	glog.Infoln(r.FormValue("uploadfile"))
	file, _, err := r.FormFile("uploadfile")
	if err != nil {
		glog.Infoln(err)
		w.Write([]byte("Error:Upload Error."))
		return
	}
	glog.Infoln(file)
	defer file.Close()

	//检测文件类型
	buff := make([]byte, 512)
	_, err = file.Read(buff)
	if err != nil {
		glog.Infoln(err)
		w.Write([]byte("Error:Upload Error."))
		return
	}
	filetype := http.DetectContentType(buff)
	glog.Infoln(filetype)
	//	if filetype != "image/jpeg" {
	//		w.Write([]byte("Error:Not JPEG."))
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
		imgid = this.makeImageID()
		glog.Infoln(imgid)
		if !this.fileExist(this.imageID2Path(imgid)) {
			break
		}
	}
	//提前创建整棵存储树
	if err = this.buildTree(imgid); err != nil {
		glog.Infoln(err)
	}
	glog.Infoln((imgid))
	f, err := os.OpenFile(this.imageID2Path(imgid), os.O_WRONLY|os.O_CREATE, 0666)
	glog.Infoln(err)
	if err != nil {
		glog.Infoln(err)
		w.Write([]byte("Error:Save Error."))
		return
	}
	defer f.Close()
	io.Copy(f, file)

	gossdb.C().Hset(data.KEY_USER+user.Userid, "Photo", imgid)
	//	user.UpdatePhoto()
	w.Write([]byte(imgid))
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
	imgpath := this.imageID2Path(imageid)
	if !this.fileExist(imgpath) {
		w.Write([]byte("Error:Image Not Found."))
		return
	}
	http.ServeFile(w, r, imgpath)
}

func (this *Imageserver) homeHandler(w http.ResponseWriter, r *http.Request) {
	w.Write([]byte(`<html> <head></head> <body> <form action="/" method="post" enctype="multipart/form-data"> 用户名 ：<input type="text" name="userid" value="" />密码 ：<input type="password" name="password" value="" /><input type="file" name="uploadfile" value="" /> <input type="submit" name="submit" /> </form> </body> </html>`))
}
