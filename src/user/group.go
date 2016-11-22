package user

import (
	"basic/ssdb/gossdb"
	"data"
	"net/http"
	"strconv"

	"github.com/labstack/echo"
)

type Group struct {
	Id    int64
	Name  string
	Desc  string
	Power int
}

func (this *Group) Get() error {
	value, err := gossdb.C().Hget(data.USER_GROUP, strconv.FormatInt(this.Id, 10))
	if err == nil {
		err = value.As(this)
	}
	return err
}

func (this *Group) Save() error {
	size, _ := gossdb.C().Hsize(data.USER_GROUP)
	size++
	this.Id = size
	err := gossdb.C().Hset(data.USER_GROUP, strconv.FormatInt(this.Id, 10), this)
	return err
}

func ListGroup() []*Group {
	list := make([]*Group, 0)
	value, err := gossdb.C().Hscan(data.USER_GROUP, "", "", 50)
	if err == nil {
		for _, v := range value {
			data := &Group{}
			if err := v.As(data); err == nil {
				list = append(list, data)
			}
		}
	}
	return list
}
func (this *Group) Del() error {
	err := gossdb.C().Hdel(data.USER_GROUP, strconv.FormatInt(this.Id, 10))
	return err
}
func DeleteGroup(c echo.Context) error {
	id, _ := strconv.ParseInt(c.FormValue("Id"), 10, 64) // string
	group := &Group{Id: id}
	if err := group.Del(); err != nil {

		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "删除组失败"})
	} else {

		return c.JSON(http.StatusOK, data.H{"status": "ok"})
	}
}
func CreateGroup(c echo.Context) error {
	name := c.FormValue("Name")                    // string
	desc := c.FormValue("Desc")                    // string
	power, _ := strconv.Atoi(c.FormValue("Power")) // string
	group := &Group{
		Name:  name,
		Desc:  desc,
		Power: power,
	}
	if err := group.Save(); err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "创建组失败"})
	} else {
		return c.JSON(http.StatusOK, data.H{"status": "ok"})
	}
}
func EditGroup(c echo.Context) error {
	id, _ := strconv.ParseInt(c.FormValue("Id"), 10, 64) // string
	name := c.FormValue("Name")                          // string
	desc := c.FormValue("Desc")                          // string

	power, _ := strconv.Atoi(c.FormValue("Power")) // string
	group := &Group{Id: id}
	if err := group.Get(); err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "没有该组"})
	} else {
		if desc != "" {
			group.Desc = desc
		}
		if power > 0 {
			group.Power = power
		}
		if name != "" {
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
	list := ListGroup()
	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": list, "count": len(list)}})
}
