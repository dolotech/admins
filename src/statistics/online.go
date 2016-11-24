package statistics

import (
	"basic/utils"
	"data"
	"net/http"
	"strconv"

	"github.com/labstack/echo"
)

func Online(c echo.Context) error {
	unix, _ := strconv.ParseInt(c.FormValue("Unix"), 10, 64) // string
	today := utils.TimestampToday()

	if unix <= 0 {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "日期超出"})
	} else if unix > today {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "日期超出"})
	}
	list, err := data.GetOnlineStatitics(c.FormValue("Unix"))
	if err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "没有该天的在线数据"})
	}
	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": list})
}
