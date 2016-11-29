/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-03-18 10:16
 * Filename      : users.go
 * Description   :
 * *******************************************************/
package user

import (
	"data"
	"net/http"
	"strconv"

	"github.com/golang/glog"
	"github.com/labstack/echo"
)

func Login(c echo.Context) error {
	username := c.FormValue("username")
	password := c.FormValue("password")

	glog.Infoln("username:", username, "password:", password)
	if len(username) == 0 || len(password) == 0 {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "账号或密码不能为空"})
	}
	u := &data.Admin{Id: username}
	if err := u.Get(); err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "密码或者账户错误"})
	}
	if password != u.Passwd {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "密码或者账户错误"})
	}

	session := &data.Session{Username: username, Password: password}
	key := data.Sessions.Add(session)
	cookie := &http.Cookie{Path: "/", Name: "login", Value: key}
	c.SetCookie(cookie)
	cookie = &http.Cookie{Path: "/", Name: "version", Value: data.Conf.Version}
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
	u := &data.Admin{}
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
		adrd := &data.AdminRecord{
			AdminID: data.GetCurrentUserID(c),
			Kind:    data.OPERATE_KIND_ADD_ADMIN,
			Target:  u.Id,
		}
		adrd.Save()

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
	lists, _ := data.UserList()
	glog.Infoln(len(lists))
	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": lists})
}

// 获取当前管理员的信息
func GetSelfDetail(c echo.Context) error {
	user := data.GetCurrentUser(c)
	if user != nil {
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

	u := &data.Admin{}
	u.Id = c.FormValue("Id")

	err := u.Get()
	if err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "用户不存在"})
	}

	if c.FormValue("Name") != "" {
		adrd := &data.AdminRecord{
			AdminID: data.GetCurrentUserID(c),
			Kind:    data.OPERATE_KIND_MODIFY_ADMIN,
			Target:  u.Id,
			Desc:    "名字",
			After:   c.FormValue("Name"),
			Pre:     u.Name,
		}
		adrd.Save()

		u.Name = c.FormValue("Name")
	}
	if c.FormValue("Passwd") != "" {
		u.Passwd = c.FormValue("Passwd")
		adrd := &data.AdminRecord{
			AdminID: data.GetCurrentUserID(c),
			Kind:    data.OPERATE_KIND_MODIFY_ADMIN,
			Target:  u.Id,
			Desc:    "密码",
		}
		adrd.Save()

	}
	if c.FormValue("Ip_limit") != "" {
		adrd := &data.AdminRecord{
			AdminID: data.GetCurrentUserID(c),
			Kind:    data.OPERATE_KIND_MODIFY_ADMIN,
			Target:  u.Id,
			Pre:     u.Ip_limit,
			After:   c.FormValue("Ip_limit"),
			Desc:    "IP限制",
		}
		adrd.Save()

		u.Ip_limit = c.FormValue("Ip_limit")
	}
	if c.FormValue("Group_id") != "" {
		adrd := &data.AdminRecord{
			AdminID: data.GetCurrentUserID(c),
			Kind:    data.OPERATE_KIND_MODIFY_ADMIN,
			Target:  u.Id,
			Pre:     strconv.FormatInt(u.Group_id, 10),
			After:   c.FormValue("Group_id"),
			Desc:    "所属组",
		}
		adrd.Save()

		u.Group_id, _ = strconv.ParseInt(c.FormValue("Group_id"), 10, 64)
	}
	if c.FormValue("Description") != "" {
		adrd := &data.AdminRecord{
			AdminID: data.GetCurrentUserID(c),
			Kind:    data.OPERATE_KIND_MODIFY_ADMIN,
			Target:  u.Id,
			Desc:    "描述",
		}
		adrd.Save()

		u.Description = c.FormValue("Description")
	}

	if err := u.Save(); err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "用户数据修改失败"})
	}
	return c.JSON(http.StatusOK, data.H{"status": "ok", "msg": "用户更改成功"})
}
func Delete(c echo.Context) error {
	u := &data.Admin{}
	u.Id = c.FormValue("Id")
	if u.Id != "" && data.ADMIN != u.Id {
		if u.Del() == nil {
			adrd := &data.AdminRecord{
				AdminID: data.GetCurrentUserID(c),
				Kind:    data.OPERATE_KIND_DEL_ADMIN,
				Target:  u.Id,
			}
			adrd.Save()

			return c.JSON(http.StatusOK, data.H{"status": "ok", "msg": "删除成功"})
		} else {
			return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "删除失败"})
		}
	} else {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "账号不能为空"})
	}
	return nil
}
