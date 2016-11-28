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
	"net/http"
	"strconv"
	"time"

	"github.com/golang/glog"
	"github.com/labstack/echo"
)

var ADMIN = "yisen@qq.com"
var admin = &User{Id: ADMIN, Passwd: utils.Md5("yisen2016"), Name: "超级管理员", Group_id: 1}

var groupList = []Group{{1, "超级管理员", "拥有最高级权限", 1}, {2, "管理员", "拥有管理玩家权限", 2}}

//
func InitAdmin() {
	for _, v := range groupList {
		boolean, _ := gossdb.C().Hexists(data.USER_GROUP, strconv.FormatInt(v.Id, 10))
		if !boolean {
			v.Save()
		}
	}
	if !admin.Exist() {
		admin.Save()
	}

}

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
	Id          string //登录账号
	Status      uint32 // '激活状态',
	Passwd      string // '密码(md5)',
	Name        string // '真实姓名',
	Description string // '描述',
	Last_visit  uint32 // '最后登录时间',
	Last_ip     uint32 // '最后登录点IP',
	Login_times uint32 // '登录次数',
	Group_id    int64  // '所属用户组ID',
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
	value, err := gossdb.C().Hget(data.USERS, u.Id)
	if err != nil {
		return err
	}
	return value.As(u)
}

// 检测管理员是否存在
func (u *User) Exist() bool {
	boolean, _ := gossdb.C().Hexists(data.USERS, u.Id)
	return boolean
}
func (u *User) Save() error {
	u.Create_time = uint32(time.Now().Unix())
	return gossdb.C().Hset(data.USERS, u.Id, u)
}
func (u *User) Del() error {
	return gossdb.C().Hdel(data.USERS, u.Id)
}
func UserList() ([]*User, error) {
	list := make([]*User, 0)
	value, err := gossdb.C().MultiHgetAll(data.USERS)
	if err != nil {
		return list, err
	}
	for _, v := range value {
		data := &User{}
		if err := v.As(data); err == nil && data.Id != ADMIN {
			list = append(list, data)
		} else {
			glog.Errorln(err)
		}
	}
	return list, nil
}

func Delete(c echo.Context) error {
	u := &User{}
	u.Id = c.FormValue("Id")
	if u.Id != "" && ADMIN != u.Id {
		if u.Del() == nil {
			return c.JSON(http.StatusOK, data.H{"status": "ok", "msg": "删除成功"})
		} else {
			return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "删除失败"})
		}
	} else {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "账号不能为空"})
	}
	return nil
}

func Login(c echo.Context) error {
	username := c.FormValue("username")
	password := c.FormValue("password")

	glog.Infoln("username:", username, "password:", password)
	if len(username) == 0 || len(password) == 0 {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "账号或密码不能为空"})
	}
	user := &User{Id: username}
	if err := user.Get(); err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "密码或者账户错误"})
	}
	if password != user.Passwd {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "密码或者账户错误"})
	}

	session := &data.Session{Username: username, Password: password}
	key := data.Sessions.Add(session)
	cookie := &http.Cookie{Path: "/", Name: "login", Value: key}
	c.SetCookie(cookie)
	return c.JSON(http.StatusOK, data.H{"status": "ok"})
}

func Logout(c echo.Context) error {
	key, _ := c.Cookie("login")
	glog.Infoln(key)
	//	session := &data.Session{}
	if key != nil {
		//		session.Del(key.Value)
		data.Sessions.Del(key.Value)
	}
	cookie := &http.Cookie{Path: "/", Name: "login", Value: ""}
	c.SetCookie(cookie)

	return c.JSON(http.StatusOK, data.H{"status": "ok", "msg": "成功退出登录"})
}

func Create(c echo.Context) error {
	if c.FormValue("Id") == "" {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "用户名不能为空"})
	}
	if c.FormValue("Name") == "" {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "真实名字不能为空"})
	}
	if c.FormValue("Passwd") == "" {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "密码不能为空"})
	}
	if c.FormValue("Passwd") != c.FormValue("Passwd1") {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "两次密码不一致"})
	}

	password := c.FormValue("Passwd")
	password1 := c.FormValue("Passwd1")
	u := &User{}
	u.Id = c.FormValue("Id")
	u.Name = c.FormValue("Name")
	u.Passwd = password
	u.Ip_limit = c.FormValue("Ip_limit")
	u.Group_id, _ = strconv.ParseInt(c.FormValue("Group_id"), 10, 64)
	u.Description = c.FormValue("Description")

	glog.Infoln("password:", password, " password1:", password1, "group_id:", u.Group_id, "ip_limit:", u.Ip_limit, "username:", u.Id, "name:", u.Name)

	if u.Exist() {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "用户名已经存在"})
	} else {
		err := u.Save()
		if err != nil {
			return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "用户创建失败"})
		} else {

			return c.JSON(http.StatusOK, data.H{"status": "ok", "msg": "用户创建成功"})
		}

	}
	return nil
}
func List(c echo.Context) error {
	lists, _ := UserList()
	glog.Infoln(len(lists))
	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": lists})
}

// 获取当前管理员的信息
func GetSelfDetail(c echo.Context) error {
	cookie, err := c.Cookie("login")
	if err == nil && cookie != nil && len(cookie.Value) > 0 {
		detail := data.Sessions.Get(cookie.Value)
		glog.Infoln(detail.Username)
		user := &User{Id: detail.Username}
		user.Passwd = ""
		if err := user.Get(); err != nil {
			return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "user not exist"})
		}
		return c.JSON(http.StatusOK, data.H{"status": "ok", "data": user})
	}
	return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "cookie is nil"})

}
func Edit(c echo.Context) error {
	if c.FormValue("Id") == "" {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "用户名不能为空"})
	}
	if c.FormValue("Name") == "" {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "真实名字不能为空"})
	}

	if c.FormValue("Passwd") != "" {
		if c.FormValue("Passwd") != c.FormValue("Passwd1") {
			return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "两次密码不一致"})
		}
	}

	u := &User{}
	u.Id = c.FormValue("Id")

	err := u.Get()
	if err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "用户不存在"})
	}

	if c.FormValue("Name") != "" {
		u.Name = c.FormValue("Name")
	}
	if c.FormValue("Passwd") != "" {
		u.Passwd = c.FormValue("Passwd")
	}
	if c.FormValue("Ip_limit") != "" {
		u.Ip_limit = c.FormValue("Ip_limit")
	}
	if c.FormValue("Group_id") != "" {
		u.Group_id, _ = strconv.ParseInt(c.FormValue("Group_id"), 10, 64)
	}
	if c.FormValue("Description") != "" {
		u.Description = c.FormValue("Description")
	}

	if err := u.Save(); err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "用户数据修改失败"})
	}
	return c.JSON(http.StatusOK, data.H{"status": "ok", "msg": "用户更改成功"})
}
