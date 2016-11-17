package operation

import (
	"basic/ssdb/gossdb"
	"data"
	"net/http"
	"resource"
	"strconv"
	"strings"
	"time"

	"github.com/gin-gonic/gin"
	"github.com/golang/glog"
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

func IssuePropsList(c *gin.Context) {
	page, _ := strconv.Atoi(c.PostForm("Page")) // string
	if page == 0 {
		page = 1
	}
	pageMax, _ := strconv.Atoi(c.PostForm("PageMax")) // string
	if pageMax == 0 {
		pageMax = 30
	}

	list, size, err := GetIssuePropsList(((page - 1) * pageMax), pageMax)
	if err != nil || size == 0 {
		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "列表为空"})
		return
	}

	data := make(map[string]interface{})
	data["list"] = list
	data["count"] = size
	c.JSON(http.StatusOK, gin.H{"status": "ok", "data": data})

}
func IssueProps(c *gin.Context) {
	userids := c.PostForm("UserIds")                        // string
	count, _ := strconv.Atoi(c.PostForm("Count"))           // uint32
	vip, _ := strconv.Atoi(c.PostForm("VIP"))               // uint32
	widgetType, _ := strconv.Atoi(c.PostForm("WidgetType")) // uint32
	if vip > 0 {
		count = 1
		widgetType = vip
	}

	userIds := strings.Split(userids, ",")
	if len(userIds) == 0 {
		glog.Infoln(userIds, count, widgetType)
		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "发放玩家ID为空"})
		return
	}
	for _, v := range userIds {
		if _, err := strconv.Atoi(v); err != nil {
			c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "玩家ID格式不对"})
			return
		}
	}
	errorList := ""
	for _, v := range userIds {
		issue := &IssuePropsRecord{}
		err := resource.ChangeRes(v, uint32(widgetType), int32(count))

		if err != nil {
			errorList += (v + ",")
		} else {
			issue.Save()
		}

	}
	if len(errorList) > 0 {
		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "以下ID出错" + errorList})

	} else {
		c.JSON(http.StatusOK, gin.H{"status": "ok"})
	}
}
