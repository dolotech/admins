/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-03-18 10:16
 * Filename      : users.go
 * Description   :
 * *******************************************************/
package user

import (
	"basic/ssdb/gossdb"
	"basic/utils"
	"data"
	"fmt"
	"net/http"
	"strconv"
	"sync"
	"time"

	"github.com/gin-gonic/contrib/sessions"
	"github.com/gin-gonic/gin"
	"github.com/golang/glog"
)

// 后台管理日志 admin_log
type user_log struct {
	Id         string
	Admin_name string // `管理员名称`
	Event      uint32 // `1登录后台`
	Ctime      int64
	Ip         string
	Memo       string
}

// 后台用户组 base_admin_user_group
type user_group struct {
	Id   string
	Name string // '用户组名称',
	Menu string // '菜单权限id,,',
}

// 后台管理员帐号base_admin_user
type User struct {
	Id          string
	Status      uint32 // '激活状态',
	Passwd      string // '密码(md5)',
	Name        string // '真实姓名',
	Description string // '描述',
	Last_visit  uint32 // '最后登录时间',
	Last_ip     uint32 // '最后登录点IP',
	Login_times uint32 // '登录次数',
	Group_id    string // '所属用户组ID',
	Ip_limit    string // 限制登录的IP
	Error_ip    string // '出错的ip',
	Error_time  int64  // '出错时间',
	Error_num   uint32 // '出错次数',
	Members     string // '属下成员后台登录名称',
	Platforms   string
	Create_time uint32 // 账户创建时间
}

var GROUPIDS = map[string]string{
	"1": "超级管理员",
	"2": "普通管理员",
}

func (u *User) Get() error {
	return gossdb.C().GetObject(data.USERS+u.Id, u)
}
func (u *User) MultiHsetSave(kvs map[string]interface{}) error {
	return gossdb.C().MultiHset(data.USERS+u.Id, kvs)
}
func (u *User) Save() error {
	err := gossdb.C().Hset(data.USERS_INDEX, u.Id, u.Id)
	if err == nil {
		return gossdb.C().PutObject(data.USERS+u.Id, u)
	}
	return err
}

func Delete(c *gin.Context) {

	u := &User{}
	u.Id = c.PostForm("Id")
	glog.Infoln("id:", u.Id)
	if u.Id != "" {
		if u.Del() == nil {
			c.JSON(http.StatusOK, gin.H{"status": "ok", "msg": "删除成功"})
		} else {
			c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "删除失败"})
		}
	} else {
		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "账号不能为空"})
	}

}
func (u *User) Del() error {
	err := gossdb.C().Hdel(data.USERS_INDEX, u.Id)
	if err == nil {
		return gossdb.C().Hclear(data.USERS + u.Id)
	}

	return err
}
func Login(c *gin.Context) {
	username := c.PostForm("username")
	password := c.PostForm("password")

	glog.Infoln("username:", username, "password:", password)
	if len(username) == 0 || len(password) == 0 {
		glog.Infoln(c.Request.URL.Path)
		//		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "账号或密码不能为空"})

	} else {
		//		user := &User{Id: username}
		//		if user.Get() == nil {
		t := time.Now().Unix()
		//if user.Passwd == utils.Md5(password) {
		keys := utils.Md5(username + password + strconv.Itoa(int(t)))
		session := sessions.Default(c)
		session.Set("loginsession", keys)
		session.Save()

		//c.JSON(http.StatusOK, gin.H{"status": "ok", "msg": "登录成功"})
		glog.Infoln("========================")

		c.Redirect(http.StatusMovedPermanently, "/roles/list.html")
		return
		//	} else {

		//	}
		//		} else {

		//		}
		//		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "密码或者账户错误"})
	}
}

func Logout(c *gin.Context) {
	glog.Infoln(c.Request.URL.Path)
	session := sessions.Default(c)
	glog.Infoln("退出登录", session.Get("loginsession"))
	session.Set("loginsession", "")
	session.Clear()
	session.Save()
	//c.JSON(http.StatusOK, gin.H{"status": "ok", "msg": "成功退出登录"})
	c.Redirect(http.StatusOK, "/users/login.html")
}

func Create(c *gin.Context) {
	if c.PostForm("Id") == "" {
		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "用户名不能为空"})
		return
	}
	if c.PostForm("Name") == "" {
		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "真实名字不能为空"})
		return
	}
	if c.PostForm("Passwd") == "" {
		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "密码不能为空"})
		return
	}
	if c.PostForm("Passwd") != c.PostForm("Passwd1") {
		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "两次密码不一致"})
		return
	}

	password := c.PostForm("Passwd")
	password1 := c.PostForm("Passwd1")
	u := &User{}
	u.Id = c.PostForm("Id")
	u.Name = c.PostForm("Name")
	u.Passwd = utils.Md5(password)
	u.Ip_limit = c.PostForm("Ip_limit")
	u.Group_id = c.PostForm("Group_id")
	u.Description = c.PostForm("Description")

	glog.Infoln("password:", password, " password1:", password1, "group_id:", u.Group_id, "ip_limit:", u.Ip_limit, "username:", u.Id, "name:", u.Name)

	val, err := GetUsersIndex(u.Id)
	glog.Infoln(val, err, len(val))
	if len(val) != 0 {
		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "用户名已经存在"})
	} else {
		u.Create_time = uint32(time.Now().Unix())
		err = u.Save()
		if err != nil {
			c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "用户创建失败"})
		} else {

			c.JSON(http.StatusOK, gin.H{"status": "ok", "msg": "用户创建成功"})
		}

	}
}
func List(c *gin.Context) {
	lists := GetMultiUser()
	c.JSON(http.StatusOK, gin.H{"status": "ok", "data": lists})
}
func Edit(c *gin.Context) {
	if c.PostForm("Id") == "" {
		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "用户名不能为空"})
		return
	}
	if c.PostForm("Name") == "" {
		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "真实名字不能为空"})
		return
	}

	if c.PostForm("Passwd") != "" {
		if c.PostForm("Passwd") != c.PostForm("Passwd1") {
			c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "两次密码不一致"})
			return
		}
	}

	m := make(map[string]interface{})
	m["ID"] = c.PostForm("Id")

	u := &User{}
	u.Id = c.PostForm("Id")

	if c.PostForm("name") != "" {
		m["Name"] = c.PostForm("name")
	}
	m["Passwd "] = utils.Md5(c.PostForm("Passwd"))
	if c.PostForm("ip_limit") != "" {
		m["Ip_limit "] = c.PostForm("ip_limit")
	}
	if c.PostForm("group_id") != "" {

		m["Group_id "] = c.PostForm("group_id")
	}
	if c.PostForm("description") != "" {
		m["Description "] = c.PostForm("description")
	}
	err := u.MultiHsetSave(m)
	if err != nil {
		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "用户数据修改失败"})
	} else {

		c.JSON(http.StatusOK, gin.H{"status": "ok", "msg": "用户更改成功"})
	}
}

func (u *User) Setpwd(c *gin.Context) {
	// username := c.PostForm("username")
	// realname := c.PostForm("name")
	// passwd := c.PostForm("passwd")
	// passwd1 := c.PostForm("passwd1")
	c.HTML(http.StatusOK, "set_password.html", gin.H{
		"data": u,
	})
}

func (u *User) Setpasswd(c *gin.Context) {
	c.HTML(http.StatusOK, "set_password.html", gin.H{
		"data": u,
	})
}

func (u *User) GroupList(c *gin.Context) {
	email := c.PostForm("email")
	password := c.PostForm("password")
	fmt.Println("email:", email, "password:", password)
	c.HTML(http.StatusOK, "group_list.html", gin.H{
		"csrfToken": "",
	})
}

func (u *User) GroupEdit(c *gin.Context) {
	email := c.PostForm("email")
	password := c.PostForm("password")
	fmt.Println("email:", email, "password:", password)
	c.HTML(http.StatusOK, "group_edit.html", gin.H{
		"csrfToken": "",
	})
}

func (u *User) LoginDemo(c *gin.Context) {
	email := c.PostForm("email")
	password := c.PostForm("password")
	fmt.Println("email:", email, "password:", password)
	c.HTML(http.StatusOK, "user_form.html", gin.H{
		"csrfToken": "",
	})
}

func (u *User) RegisterDemo(c *gin.Context) {
	c.HTML(http.StatusOK, "user_form.html", gin.H{
		"new":       true,
		"csrfToken": "",
	})
}

var tex sync.Mutex

// err = gossdb.C().Set(USER_LOG_INDEX, id)
// value, err := gossdb.C().Get(USER_LOG_INDEX)
func GetLogIndex() (string, error) {
	tex.Lock()
	value, err := gossdb.C().Get(data.USER_LOG_INDEX)
	index := string(value)
	if index == "" {
		index = "1"
	}
	err = gossdb.C().Set(data.USER_LOG_INDEX, utils.StringAdd(index))
	tex.Unlock()
	return index, err
}

// err = gossdb.C().Hset(USER_GROUP_INDEX, Name, id)
// value, err := gossdb.C().Hget(USER_GROUP_INDEX, Name)
func GetGroupIndex(name string) (string, error) {
	value, err := gossdb.C().Hget(data.USER_GROUP_INDEX, name)
	return string(value), err
}

func SetGroupIndex(name, id string) error {
	return gossdb.C().Hset(data.USER_GROUP_INDEX, name, id)
}

func GetGroupIndexSize() (int64, error) {
	return gossdb.C().Hsize(data.USER_GROUP_INDEX)
}

// err = gossdb.C().Hset(USERS_INDEX, Name, id)
// value, err := gossdb.C().Hget(USERS_INDEX, Name)
func GetUsersIndex(name string) (string, error) {
	value, err := gossdb.C().Hget(data.USERS_INDEX, name)
	return string(value), err
}

func GetUsersIndexSize() (int64, error) {
	return gossdb.C().Hsize(data.USERS_INDEX)
}

func GetMultiUser() []*User {
	userids, _ := gossdb.C().MultiHgetAll(data.USERS_INDEX)
	usersL := make([]*User, 0, len(userids))
	for k, _ := range userids {
		user := &User{Id: k}
		if err := user.Get(); err != nil {
			fmt.Println(err)
		}
		usersL = append(usersL, user)
	}
	return usersL
}