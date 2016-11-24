package operation

import (
	"basic/ssdb/gossdb"
	"data"
	"net/http"
	"strconv"

	"github.com/golang/glog"
	"github.com/labstack/echo"
)

// 获取比赛场记录
func MatchRecord(c echo.Context) error {
	page, _ := strconv.Atoi(c.FormValue("Page")) // string
	if page < 1 {
		page = 1
	}
	pageMax, _ := strconv.Atoi(c.FormValue("PageMax")) // string
	if pageMax < 30 {
		pageMax = 30
	} else if pageMax > 200 {
		pageMax = 200
	}
	userid := c.FormValue("Userid")
	createTime := c.FormValue("Create_time")
	if createTime == "" {
		list, count, _ := data.GetMatchRecord(userid, ((page - 1) * pageMax), pageMax)
		return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": list, "count": count}})
	} else {
		list, _ := data.GetMatchDestopRecord(userid, createTime)
		count := 4
		return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": list, "count": count}})
	}
}

// 获取私人局记录
func PrivateRecord(c echo.Context) error {
	page, _ := strconv.Atoi(c.FormValue("Page")) // string
	if page < 1 {
		page = 1
	}
	pageMax, _ := strconv.Atoi(c.FormValue("PageMax")) // string
	if pageMax < 30 {
		pageMax = 30
	} else if pageMax > 200 {
		pageMax = 200
	}
	userid := c.FormValue("Userid")
	glog.Infoln(c.Param("Userid"), c.QueryParam("Userid"), c.QueryString())
	createTime := c.FormValue("Create_time")
	if createTime == "" {
		list, count, _ := data.GetPrivateRecord(userid, ((page - 1) * pageMax), pageMax)
		return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": list, "count": count}})
	} else {
		list, _ := data.GetPrivateDestopRecord(userid, createTime)
		count := 4
		return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": list, "count": count}})
	}
}

// 获取金币场牌局记录
func NormalRecord(c echo.Context) error {
	page, _ := strconv.Atoi(c.FormValue("Page")) // string
	if page < 1 {
		page = 1
	}
	pageMax, _ := strconv.Atoi(c.FormValue("PageMax")) // string
	if pageMax < 30 {
		pageMax = 30
	} else if pageMax > 200 {
		pageMax = 200
	}
	userid := c.FormValue("Userid")
	createTime := c.FormValue("Create_time")
	if createTime == "" {
		list, count, _ := data.GetNormalRecord(userid, ((page - 1) * pageMax), pageMax)
		return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": list, "count": count}})
	} else {
		list, _ := data.GetDestopRecord(userid, createTime)
		count := 4
		return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": list, "count": count}})
	}
}

type CardRecodeStr struct {
	Value uint32
	Seat  uint32
	Card  uint32
}

func CardRecode(c echo.Context) error {
	index := c.FormValue("index")
	glog.Infoln(index)
	value, err := gossdb.C().Hget(data.KEY_CARD_RECORD, index)
	glog.Infoln(value, err)
	list := make([]uint64, 0)
	glog.Infoln(value, err)
	if err == nil {
		value.As(&list)
	}

	array := make([]*CardRecodeStr, len(list))
	for i := 0; i < len(list); i++ {
		v := list[i]
		data := &CardRecodeStr{
			Value: uint32(v >> 32),
			Seat:  uint32(v & 0xFF),
			Card:  uint32(v >> 8 & 0xff),
		}
		array[i] = data
	}

	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": array})
}
