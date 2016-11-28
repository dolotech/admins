package operation

import (
	"basic/ssdb/gossdb"
	"data"
	"net/http"
	"resource"
	"strconv"
	"strings"
	"time"

	"github.com/golang/glog"
	"github.com/labstack/echo"
)

type IssuePropsRecord struct {
	AdminID    string
	Count      uint32
	WidgetType uint32 //
	UserID     string
	Desc       string
	Time       uint32
}

func GetIssuePropsList(offset, limit int) ([]*IssuePropsRecord, int64, error) {
	count, err := gossdb.C().Qsize(data.PROPS_ISSUE_LOG)
	if err != nil {
		return nil, 0, err
	}
	value, err := gossdb.C().Qrange(data.PROPS_ISSUE_LOG, offset, limit)
	if err != nil {
		return nil, 0, err
	}
	list := make([]*IssuePropsRecord, len(value))
	for i := 0; i < len(value); i++ {
		data := &IssuePropsRecord{}
		value[i].As(data)
		list[i] = data
	}
	return list, count, nil
}
func (this *IssuePropsRecord) Save() error {
	this.Time = uint32(time.Now().Unix())
	_, err := gossdb.C().Qpush_front(data.PROPS_ISSUE_LOG, this)
	if err != nil {
		return err
	}
	return nil
}

func IssuePropsList(c echo.Context) error {
	page, _ := strconv.Atoi(c.FormValue("Page")) // string
	if page == 0 {
		page = 1
	}
	pageMax, _ := strconv.Atoi(c.FormValue("PageMax")) // string
	if pageMax == 0 {
		pageMax = 30
	}

	list, size, err := GetIssuePropsList(((page - 1) * pageMax), pageMax)
	if err != nil || size == 0 {
		c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "列表为空"})
		return nil
	}

	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": list, "count": size}})
}
func IssueProps(c echo.Context) error {
	userids := c.FormValue("UserIds")                        // string
	count, _ := strconv.Atoi(c.FormValue("Count"))           // uint32
	vip, _ := strconv.Atoi(c.FormValue("VIP"))               // uint32
	widgetType, _ := strconv.Atoi(c.FormValue("WidgetType")) // uint32
	desc := c.FormValue("Desc")
	glog.Infoln(userids, count, widgetType, desc)

	if widgetType == 14 {
		count = 1
		widgetType = vip
	}

	userIds := strings.Split(userids, ",")
	if len(userIds) == 0 {
		glog.Infoln(userIds, count, widgetType)
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "发放玩家ID为空"})
	}
	for _, v := range userIds {
		if _, err := strconv.Atoi(v); err != nil {
			c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "玩家ID格式不对"})
			return nil
		}
	}
	errorList := ""
	cookie, err := c.Cookie("login")
	var username = ""
	if err == nil && cookie != nil || len(cookie.Value) > 0 {
		se := data.Sessions.Get(cookie.Value)
		if se != nil {
			username = se.Username
		}
	}
	for _, v := range userIds {
		issue := &IssuePropsRecord{AdminID: username, WidgetType: uint32(widgetType), UserID: v, Count: uint32(count), Desc: desc}
		err := resource.ChangeRes(v, uint32(widgetType), int32(count))
		if err != nil {
			errorList += (v + ",")
			glog.Errorln(err)
		} else {
			issue.Save()
		}

	}
	if len(errorList) > 0 {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "以下ID出错" + errorList})

	} else {
		return c.JSON(http.StatusOK, data.H{"status": "ok"})
	}
	return nil
}
