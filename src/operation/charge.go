package operation

import (
	"basic/utils"
	"data"
	"net/http"
	"strconv"

	"github.com/golang/glog"
	"github.com/labstack/echo"
)

// 交易记录
func GetTransition(c echo.Context) error {
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
	timestamp := c.FormValue("Unix")
	if timestamp == "" {
		timestamp = utils.TimestampTodayStr()
	}
	userid := c.FormValue("Userid")
	var size int64 = 0
	var list []*data.TradingResults
	var err error
	if userid != "" {
		list, size, err = data.GetTransitionByUserid(timestamp, userid, (page-1)*pageMax, pageMax)
	} else {
		list, size, err = data.GetTransition(timestamp, (page-1)*pageMax, pageMax)
	}

	if err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "列表为空"})
	}
	return c.JSON(http.StatusOK, data.H{"status": "ok", "list": list, "count": size})
}

// 下单记录
func GetChargeOrder(c echo.Context) error {
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
	timestamp := c.FormValue("Unix")
	if timestamp == "" {
		timestamp = utils.TimestampTodayStr()
	}

	userid := c.FormValue("Userid")
	glog.Infoln(userid != "", timestamp)
	var size int64 = 0
	var list []*data.ChargeOrder
	var err error
	if userid != "" {
		list, size, err = data.GetChargeOrderByUserid(timestamp, userid, (page-1)*pageMax, pageMax)
	} else {
		list, size, err = data.GetChargeOrder(timestamp, (page-1)*pageMax, pageMax)
	}
	if err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "列表为空"})
	}
	return c.JSON(http.StatusOK, data.H{"status": "ok", "list": list, "count": size})
}
