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
	title := c.FormValue("Title")                // string
	content := c.FormValue("Content")            // string
	kind, _ := strconv.Atoi(c.FormValue("Kind")) //1:公告，2：圈子消息，3,：奖励

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
				Kind:    uint32(kind),
			}}
		if kind == 3 {
			appendixname := ""
			post.Data.Appendix = append(post.Data.Appendix, widget)
			if widgetType == 22 {
				appendixname = "VIP1"
			} else if widgetType == 23 {
				appendixname = "VIP2"
			} else if widgetType == 24 {
				appendixname = "VIP3"
			} else if widgetType == 1 {
				appendixname = "金币x" + c.FormValue("Count")
			} else if widgetType == 2 {
				appendixname = "兑换券x" + c.FormValue("Count")
			} else if widgetType == 3 {
				appendixname = "入场券x" + c.FormValue("Count")
			} else if widgetType == 4 {
				appendixname = "钻石x" + c.FormValue("Count")
			} else if widgetType == 100 {
				appendixname = "经验x" + c.FormValue("Count")
			}
			post.Data.Appendixname = appendixname
		}

		boolean := post.Call()
		if !boolean {
			return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "send post fail ,something error"})
		}
		for _, v := range userIds {
			adrd := &data.AdminRecord{
				AdminID: username,
				Kind:    data.OPERATE_KIND_SEND_MAIL,
				Target:  v,
				Desc:    title,
			}
			if kind == 3 {
				adrd.Count = (count)
				adrd.WindgetID = uint32(widgetType)
			}
			adrd.Save()

		}
	}

	return c.JSON(http.StatusOK, data.H{"status": "ok"})
}
