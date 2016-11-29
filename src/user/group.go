package user

import (
	"basic/ssdb/gossdb"
	"data"
	"net/http"
	"strconv"

	"github.com/labstack/echo"
)

func DeleteGroup(c echo.Context) error {
	id, _ := strconv.ParseInt(c.FormValue("Id"), 10, 64) // string
	group := &data.Group{Id: id}
	group.Get()
	if err := group.Del(); err != nil {

		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "删除组失败"})
	} else {
		adrd := &data.AdminRecord{
			AdminID: data.GetCurrentUserID(c),
			Kind:    data.OPERATE_KIND_DEL_GROUP,
			Target:  group.Name,
		}
		adrd.Save()

		return c.JSON(http.StatusOK, data.H{"status": "ok"})
	}
}
func CreateGroup(c echo.Context) error {
	name := c.FormValue("Name")                    // string
	desc := c.FormValue("Desc")                    // string
	power, _ := strconv.Atoi(c.FormValue("Power")) // string
	group := &data.Group{
		Name:  name,
		Desc:  desc,
		Power: power,
	}
	boolean, _ := gossdb.C().Hexists(data.USER_GROUP_INDEX, group.Name)
	if boolean {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "管理员组名已经存在"})
	}
	if err := group.Save(); err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "创建组失败"})
	} else {
		adrd := &data.AdminRecord{
			AdminID: data.GetCurrentUserID(c),
			Kind:    data.OPERATE_KIND_ADD_GROUP,
			Target:  name,
		}
		adrd.Save()
		return c.JSON(http.StatusOK, data.H{"status": "ok"})
	}
}
func EditGroup(c echo.Context) error {
	id, _ := strconv.ParseInt(c.FormValue("Id"), 10, 64) // string
	name := c.FormValue("Name")                          // string
	desc := c.FormValue("Desc")                          // string

	power, _ := strconv.Atoi(c.FormValue("Power")) // string
	group := &data.Group{Id: id}
	if err := group.Get(); err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "没有该组"})
	} else {
		if desc != "" {
			adrd := &data.AdminRecord{
				AdminID: data.GetCurrentUserID(c),
				Kind:    data.OPERATE_KIND_MODIFY_GROUP,
				Target:  group.Name,
				Pre:     group.Desc,
				After:   desc,
				Desc:    "描述",
			}
			adrd.Save()

			group.Desc = desc
		}
		if power > 0 {
			adrd := &data.AdminRecord{
				AdminID: data.GetCurrentUserID(c),
				Kind:    data.OPERATE_KIND_MODIFY_GROUP,
				Target:  group.Name,
				Pre:     strconv.Itoa(group.Power),
				After:   strconv.Itoa(power),
				Desc:    "权限",
			}
			adrd.Save()

			group.Power = power

		}
		if name != "" {
			adrd := &data.AdminRecord{
				AdminID: data.GetCurrentUserID(c),
				Kind:    data.OPERATE_KIND_MODIFY_GROUP,
				Target:  group.Name,
				Pre:     group.Name,
				After:   name,
				Desc:    "名字",
			}
			adrd.Save()

			group.Name = name

		}

		if err := group.Save(); err != nil {
			return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "编辑组失败"})
		} else {
			return c.JSON(http.StatusOK, data.H{"status": "ok"})
		}
	}
}
func Groups(c echo.Context) error {
	list := data.ListGroup()
	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": list, "count": len(list)}})
}
