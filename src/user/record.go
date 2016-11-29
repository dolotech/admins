package user

import (
	"data"
	"net/http"
	"strconv"

	"github.com/labstack/echo"
)

// 获取管理员的操作记录
func Record(c echo.Context) error {
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

	adminID := c.FormValue("AdminID")
	if adminID == "" {
		adminID = data.GetCurrentUserID(c)
	}
	if adminID == "" {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "没有数据"})
	}

	list, size, err := data.GetAdminRecord(adminID, (page-1)*pageMax, pageMax)
	if err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "没有数据"})
	}
	return c.JSON(http.StatusOK, data.H{"status": "ok", "list": list, "count": size})
}
