/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-03-18 10:16
 * Filename      : users.go
 * Description   :
 * *******************************************************/
package admin

import (
	"basic/ssdb/gossdb"
	"basic/utils"
	"fmt"
	"net/http"
	"strconv"
	"sync"

	"github.com/gin-gonic/contrib/sessions"
	"github.com/gin-gonic/gin"
	"github.com/golang/glog"
)

var Pager pager
var Selected selected

const (
	USERS_INDEX      string = "users_index"
	USER_GROUP_INDEX string = "user_group_index"
	USER_LOG_INDEX   string = "user_log_index"
	USERS            string = "users"
	USER_GROUP       string = "user_group"
	USER_LOG         string = "user_log"
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

var Users *User = &User{}

// 后台管理员帐号base_admin_user
type User struct {
	Id          string
	Username    string // '登录名',
	Status      uint32 // '激活状态',
	Passwd      string // '密码(md5)',
	Name        string // '真实姓名',
	Description string // '描述',
	Last_visit  uint32 // '最后登录时间',
	Last_ip     string // '最后登录点IP',
	Last_addr   string // '最后登录地点',
	Login_times uint32 // '登录次数',
	Group_id    string // '所属用户组ID',
	Ip_limit    string //
	Error_ip    string // '出错的ip',
	Error_time  int64  // '出错时间',
	Error_num   uint32 // '出错次数',
	Members     string // '属下成员后台登录名称',
	Platforms   string
}

var GROUPIDS = map[string]string{
	"1": "超级管理员",
}

func (u *User) Get() error {
	return gossdb.C().GetObject(USERS+u.Id, u)
}

func (u *User) Save() error {
	return gossdb.C().PutObject(USERS+u.Id, u)
}

func (u *User) Delete() error {
	return gossdb.C().Hclear(USERS + u.Id)
}

func (u *User) Login(c *gin.Context) {
	message := "账号或密码不能为空"
	glog.Infoln(c.Request.URL.Path)
	c.HTML(http.StatusOK, "login.html", gin.H{
		"serverName": "麻将",
		"message":    message,
	})
}

func (u *User) Authenticate(c *gin.Context) {
	username := c.PostForm("username")
	password := c.PostForm("password")

	glog.Infoln("username:", username, "password:", password)
	if len(username) == 0 || len(password) == 0 {
		c.Redirect(http.StatusMovedPermanently, "/users/login")
	} else {
		//	cookie := utils.Md5(username + password)
		//	session := sessions.Default(c)
		//	session.Set("username", cookie)
		//	session.Save()
		glog.Infoln("username:", username, "password:", password)
		//		c.Request.Header.Set("Cookie", "username="+cookie)
		//		c.SetCookie(
		//			"username",
		//			cookie,
		//			864000,
		//			"/",
		//			"localhost",
		//			true,
		//			true,
		//		)
		c.Redirect(http.StatusMovedPermanently, "/roles/list")
	}
}

func (u *User) Logout(c *gin.Context) {
	glog.Infoln(c.Request.URL.Path)
	session := sessions.Default(c)
	glog.Infoln("退出登录", session.Get("username"))
	session.Clear()
	session.Save()
	c.Redirect(http.StatusMovedPermanently, "/users/login")
}

// func (u *User) User_Edit(c *gin.Context) {
// 	value, err := u.Get()
// 	c.HTML(http.StatusOK, "user_edit.html", gin.H{
// 		"serverName": "麻将",
// 		"message":    message,
// 	})
// }
//
// func (u *User) User_List(c *gin.Context) {
// 	index_size, err := GetUsersIndexSize()
// 	if err != nil {
// 		//
// 	}
// 	value, err := u.Get()
// 	c.HTML(http.StatusOK, "user_list.html", gin.H{
// 		"serverName": "麻将",
// 		"message":    message,
// 	})
// }

func (u *User) Create(c *gin.Context) {
	Selected.SetSelect("group_id", "1", GROUPIDS)
	// Emsg.validate()
	c.HTML(http.StatusOK, "create.html", gin.H{
		"users":    u,
		"emsg":     Emsg,
		"selected": Selected,
	})
}

func (u *User) Created(c *gin.Context) {
	password := c.PostForm("password")
	password1 := c.PostForm("password1")
	u.Username = c.PostForm("username")
	u.Name = c.PostForm("name")
	u.Passwd = utils.Md5(password)
	u.Ip_limit = c.PostForm("ip_limit")
	u.Group_id = c.PostForm("group_id")
	u.Description = c.PostForm("description")

	var result bool = false
	if password != password1 {
		Emsg.validate() // 两次密码不一致
	} else if len(u.Username) == 0 || len(u.Name) == 0 {
		Emsg.validate() // 名字不能为空
	} else {
		val, err := GetUsersIndex(u.Username)
		if err == nil || len(val) != 0 {
			Emsg.validate() // 用户名已存在
		} else {
			index_size, err := GetUsersIndexSize()
			if err != nil {
				fmt.Println("err:", err)
				index_size = 0
			}
			u.Id = strconv.Itoa(int(index_size + 1))
			err = SetUsersIndex(u.Username, u.Id)
			if err != nil {
				fmt.Println("err:", err)
			}
			err = u.Save()
			if err != nil {
				fmt.Println("err:", err)
			}
			result = true
		}
	}

	if result {
		c.Redirect(http.StatusMovedPermanently, "/users/list")
	} else {
		// c.Redirect(http.StatusMovedPermanently, "/users/create")
		u.Create(c)
	}
}

func (u *User) Search(c *gin.Context) {
	username := c.PostForm("username")
	realname := c.PostForm("name")
	begin := c.PostForm("last_visit_begin")
	end := c.PostForm("last_visit_end")
	val, err := GetUsersIndex(username)
	if err != nil {
		fmt.Println("err:", err)
	}
	lists := GetMultiUser([]string{val})
	//
	Selected.SetSelect("group_id", "1", GROUPIDS)
	c.HTML(http.StatusOK, "user_list.html", gin.H{
		"list":             lists,
		"username":         username,
		"name":             realname,
		"last_visit_begin": begin,
		"last_visit_end":   end,
		"selected":         Selected,
	})
}

func (u *User) List(c *gin.Context) {
	delete := c.Query("delete")
	if len(delete) > 0 {
		u.Delete()
	}
	edit := c.Query("edit")
	if len(edit) > 0 {
		u = &User{Id: edit}
		u.Edit(c)
		return
	}
	Pager.GetPager(c)
	Selected.SetSelect("group_id", "1", GROUPIDS)
	index_size, err := GetUsersIndexSize()
	if err != nil {
		fmt.Println("List err:", err)
	}
	var ids []string
	p := Pager.Page * Pager.Limit
	s := (Pager.Page-1)*Pager.Limit + 1
	id := int(index_size)
	for ; s <= p; s++ {
		ids = append(ids, strconv.Itoa(int(id-s)))
	}
	lists := GetMultiUser(ids)
	c.HTML(http.StatusOK, "user_list.html", gin.H{
		"pager":    Pager,
		"selected": Selected,
		"list":     lists,
	})
}

func (u *User) Edit(c *gin.Context) {
	err := u.Get()
	if err != nil {
		fmt.Println("Eidt err:", err)
	}
	username := c.PostForm("username")
	realname := c.PostForm("name")
	passwd := c.PostForm("passwd")
	// passwd1 := c.PostForm("passwd1")
	group_id := c.PostForm("group_id")
	ip_limit := c.PostForm("ip_limit")
	error_num := c.PostForm("error_num")
	description := c.PostForm("description")
	u.Username = username
	u.Name = realname
	u.Passwd = utils.Md5(passwd)
	u.Group_id = group_id
	u.Ip_limit = ip_limit
	num, _ := strconv.Atoi(error_num)
	u.Error_num = uint32(num)
	u.Description = description
	u.Save()
	if err != nil {
		fmt.Println("err:", err)
	}
	Emsg.validate()
	Selected.SetSelect("group_id", "1", GROUPIDS)
	c.HTML(http.StatusOK, "user_edit.html", gin.H{
		"data":     u,
		"emsg":     Emsg,
		"selected": Selected,
		"goback":   "list",
	})
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

var Emsg emsg = emsg{}

type emsg struct {
	EUsername string
	EName     string
	EPasswd   string
	EGroup_id string
}

// msg = &emsg{}
func (e *emsg) validate() {
	e.EUsername = "用户名不能为空/ 用户名已存在，请另外选择一个用户名"
	e.EName = "名字不能为空"
	e.EPasswd = "密码不能为空或你输入的两次密码不一致"
	e.EGroup_id = "不能设置为超级管理员"
}

var tex sync.Mutex

// err = gossdb.C().Set(USER_LOG_INDEX, id)
// value, err := gossdb.C().Get(USER_LOG_INDEX)
func GetLogIndex() (string, error) {
	tex.Lock()
	value, err := gossdb.C().Get(USER_LOG_INDEX)
	index := string(value)
	if index == "" {
		index = "1"
	}
	err = gossdb.C().Set(USER_LOG_INDEX, utils.StringAdd(index))
	tex.Unlock()
	return index, err
}

// err = gossdb.C().Hset(USER_GROUP_INDEX, Name, id)
// value, err := gossdb.C().Hget(USER_GROUP_INDEX, Name)
func GetGroupIndex(name string) (string, error) {
	value, err := gossdb.C().Hget(USER_GROUP_INDEX, name)
	return string(value), err
}

func SetGroupIndex(name, id string) error {
	return gossdb.C().Hset(USER_GROUP_INDEX, name, id)
}

func GetGroupIndexSize() (int64, error) {
	return gossdb.C().Hsize(USER_GROUP_INDEX)
}

// err = gossdb.C().Hset(USERS_INDEX, Name, id)
// value, err := gossdb.C().Hget(USERS_INDEX, Name)
func GetUsersIndex(name string) (string, error) {
	value, err := gossdb.C().Hget(USERS_INDEX, name)
	return string(value), err
}

func SetUsersIndex(name, id string) error {
	return gossdb.C().Hset(USERS_INDEX, name, id)
}

func GetUsersIndexSize() (int64, error) {
	return gossdb.C().Hsize(USERS_INDEX)
}

func GetMultiUser(userids []string) []*User {
	usersL := make([]*User, 0, len(userids))
	for _, v := range userids {
		user := &User{Id: v}
		if err := user.Get(); err != nil {
			fmt.Println(err)
		}
		usersL = append(usersL, user)
	}
	return usersL
}
