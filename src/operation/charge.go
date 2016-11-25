package operation

import (
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
	userid := c.FormValue("Userid")
	var size int64 = 800
	var list []*data.TradingResults
	if userid != "" {
	} else {
		for i := 0; i < 50; i++ {
			trans := &data.TradingResults{Transtype: i}
			list = append(list, trans)
		}

	}

	if len(list) == 0 {
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
	userid := c.FormValue("Userid")
	var size int64 = 800
	var list []*data.ChargeOrder
	if userid != "" {
	} else {
		for i := 0; i < 50; i++ {
			trans := &data.ChargeOrder{Orderid: "99"}
			list = append(list, trans)
		}

	}

	if len(list) == 0 {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "列表为空"})
	}
	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": list, "count": size}})
}
