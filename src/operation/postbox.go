package operation

import (
	"admincall"
	"data"
	"net/http"
	"strconv"
	"strings"

	"github.com/golang/glog"
	"github.com/labstack/echo"
)

func Postbox(c echo.Context) error {
	userids := c.FormValue("UserIds")                        // string
	count, _ := strconv.Atoi(c.FormValue("Count"))           // uint32
	vip, _ := strconv.Atoi(c.FormValue("VIP"))               // uint32
	widgetType, _ := strconv.Atoi(c.FormValue("WidgetType")) // uint32
	desc := c.FormValue("Desc")
	glog.Infoln(userids, count, widgetType, desc)
	title := c.FormValue("Title")     // string
	content := c.FormValue("Content") // string
	if widgetType == 14 {
		count = 1
		widgetType = vip
	}

	userIds := strings.Split(userids, ",")
	if len(userIds) == 0 {
		glog.Infoln(userIds, count, widgetType)
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "发送玩家ID为空"})
	}
	for _, v := range userIds {
		if _, err := strconv.Atoi(v); err != nil {
			c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "玩家ID格式不对"})
			return nil
		}
	}
	var username = data.GetCurrentUserID(c)
	if username != "" {
		widget := &data.WidgetData{Id: uint32(widgetType), Count: uint32(count)}
		post := &admincall.EmailReceiverArgs{
			Userid: userIds,
			Data: &data.DataPostbox{
				Title:   title,
				Content: content,
			}}

		post.Data.Appendix = append(post.Data.Appendix, widget)
		boolean := post.Call()
		if !boolean {
			return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "send post fail ,something error"})
		}
	}

	return c.JSON(http.StatusOK, data.H{"status": "ok"})
}
