package statistics

import (
	"basic/ssdb/gossdb"
	"basic/utils"
	"data"
	"net/http"
	"strconv"

	"github.com/golang/glog"
	"github.com/labstack/echo"
)

// 基础统计
type BasiceStatitics struct {
	Keep1  uint32 // 次日留存
	Keep3  uint32 // 3日留存
	Keep7  uint32 // 周留存
	Keep30 uint32 // 月留存

	Active      uint32 // 活跃
	NewCount    uint32 // 新增
	OnlineCount uint32 //当前在线人数

	ActiveARPU      uint32 // 活跃ARPU值=当日收入÷当日活跃用户
	ARPU            uint32 // 付费ARPU值=当日收入÷当日付费用户
	ExpenseRate     uint32 // 付费率=付费用户÷活跃用户x100%
	ExpenseAll      uint32 // 累加付费金额
	ExpenseCount    uint32 // 累加付费人数
	ExpenseNewCount uint32 // 新增付费人数
}

func Index(c echo.Context) error {
	//userid := c.FormValue("Userid")
	day := strconv.FormatInt(utils.TimestampToday(), 10)
	//   当天活跃数量
	active, err := gossdb.C().Hsize(data.KEY_ACTIVE_STATISTICS + day)
	glog.Infoln(err, active)
	//	新增数量
	newuser, err := gossdb.C().Qsize(data.KEY_NEWUSER_STATISTICS + day)
	glog.Infoln(err, newuser)
	online, err := gossdb.C().Hsize(data.KEY_ONLINE)
	if err != nil {
		glog.Errorln(err, online)

	}
	basic := &BasiceStatitics{
		Keep1:  233,
		Keep3:  233,
		Keep7:  233,
		Keep30: 233,

		Active:      uint32(active),
		NewCount:    uint32(newuser),
		ARPU:        233,
		ActiveARPU:  233,
		ExpenseRate: 233,
		OnlineCount: uint32(online),
	}
	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": basic})
}
