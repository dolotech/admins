package operation

import (
	"basic/utils"
	"net/http"
	"resource"
	"strconv"
	"strings"

	"github.com/gin-gonic/gin"
	"github.com/golang/glog"
)

type IssuePropsRecord struct {
	UserID     string
	Count      uint32
	WidgetType uint32 //
	UserIds    []string
	Desc       string
}

func IssueProps(c *gin.Context) {
	issue := &IssuePropsRecord{}
	userids := c.PostForm("UserIds")                        // string
	count, _ := strconv.Atoi(c.PostForm("Count"))           // uint32
	widgetType, _ := strconv.Atoi(c.PostForm("WidgetType")) // uint32
	glog.Infoln(userids, count, widgetType)
	issue.UserIds = strings.Split(userids, ",")
	if len(issue.UserIds) == 0 {
		c.JSON(http.StatusOK, gin.H{"status": "ok", "msg": "发放玩家ID为空"})
		return
	}
	for _, v := range issue.UserIds {
		if !utils.IsNumString(v) {
			c.JSON(http.StatusOK, gin.H{"status": "ok", "msg": "玩家ID格式不对"})
			return
		}
	}
	errorList := ""
	for _, v := range issue.UserIds {
		err := resource.ChangeRes(v, uint32(widgetType), int32(count))
		if err != nil {
			errorList += (v + ",")
		}
	}
	if len(errorList) > 0 {
		c.JSON(http.StatusOK, gin.H{"status": "ok", "msg": "一下ID出错" + errorList})
	} else {
		c.JSON(http.StatusOK, gin.H{"status": "ok"})
	}
}
