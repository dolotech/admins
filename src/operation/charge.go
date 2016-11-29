package operation

import (
	"basic/utils"
	"data"
	"net/http"
	"strconv"

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
	unix, _ := strconv.ParseInt(c.FormValue("Unix"), 10, 64) // string
	today := utils.TimestampToday()

	if unix <= 0 {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "日期超出"})
	} else if unix > today {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "日期超出"})
	}
	userid := c.FormValue("Userid")
	var size int64 = 0
	var list []*data.TradingResults
	var err error
	if userid != "" {
		list, size, err = data.GetTransitionByUserid(c.FormValue("Unix"), userid, page, pageMax)
	} else {
		list, size, err = data.GetTransition(c.FormValue("Unix"), page, pageMax)
	}

	if err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "列表为空"})
	}
	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": list, "count": size}})
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

	unix, _ := strconv.ParseInt(c.FormValue("Unix"), 10, 64) // string
	today := utils.TimestampToday()

	if unix <= 0 {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "日期超出"})
	} else if unix > today {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "日期超出"})
	}

	userid := c.FormValue("Userid")
	var size int64 = 0
	var list []*data.ChargeOrder
	var err error
	if userid != "" {
		list, size, err = data.GetChargeOrderByUserid(c.FormValue("Unix"), userid, page, pageMax)
	} else {
		list, size, err = data.GetChargeOrder(c.FormValue("Unix"), page, pageMax)
	}
	if err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "列表为空"})
	}
	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": list, "count": size}})
}
