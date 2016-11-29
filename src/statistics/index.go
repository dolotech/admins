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
	Timestamp uint32
	Keep1     uint32 // 次日留存
	Keep3     uint32 // 3日留存
	Keep7     uint32 // 周留存
	Keep30    uint32 // 月留存

	Active          uint32  // 活跃
	NewCount        uint32  // 新增
	ActiveARPU      uint32  // 活跃ARPU值=当日收入÷当日活跃用户
	ARPU            uint32  // 付费ARPU值=当日收入÷当日付费用户
	ExpenseRate     uint32  // 付费率=付费用户÷活跃用户x100%
	ExpenseAll      float32 // 累加付费金额
	ExpenseCount    uint32  // 累加付费人数
	ExpenseNewCount uint32  // 新增付费人数
}

func Index(c echo.Context) error {
	timeStampEnd := c.FormValue("UnixEnd")
	timeStampStart := c.FormValue("UnixStart")

	unixEnd, _ := strconv.ParseInt(timeStampEnd, 10, 64)     // string
	unixStart, _ := strconv.ParseInt(timeStampStart, 10, 64) // string
	if unixEnd <= 0 || unixStart <= 0 {
		//return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "日期格式不对"})
		unixEnd = utils.TimestampToday()
		unixStart = utils.TimestampToday()
	} else {
		if unixEnd > utils.TimestampToday() {
			return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "日期超出"})
		}
		if unixStart > unixEnd {
			return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "开始日期大于结束日期"})
		}
	}
	m := generateBaseStatitics(uint32(unixStart), uint32(unixEnd))
	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": m})
}

func generateBaseStatitics(start uint32, end uint32) []*BasiceStatitics {
	m := make([]*BasiceStatitics, 0, int((end-start)/86400))
	for {
		unixstr := strconv.FormatInt(int64(start), 10)
		//   当天活跃数量
		active, err := gossdb.C().Hsize(data.KEY_ACTIVE_STATISTICS + unixstr)
		if err != nil || active == 0 {
			start += 86400
			if start >= end {
				break
			}

			glog.Infoln(err, active)
			continue
		}
		//	新增数量
		newuser, err := gossdb.C().Qsize(data.KEY_NEWUSER_STATISTICS + unixstr)
		glog.Infoln(err, newuser)
		allTransition, err := data.GetAllTransition(unixstr)
		// 当天总充值金额
		var allMoney float32
		transHash := make(map[string]string)
		if err != nil {
			glog.Infoln(err)
		} else {
			//  该天总充值金额
			for _, v := range allTransition {
				transHash[v.Appuserid] = v.Appuserid
				allMoney += v.Money
			}
		}

		// 新增付费用户数量
		var newuserChargeCount int
		newusers, err := data.GetAllNewuser(unixstr)
		if err != nil {
			glog.Infoln(err)
		} else {
			for _, v := range newusers {
				if _, ok := transHash[v.Userid]; ok {
					newuserChargeCount++
				}
			}
		}

		//当日的付费玩家数量
		chargePlayerCount := len(transHash)
		ARPU := allMoney / float32(chargePlayerCount)
		activeARPU := allMoney / float32(active)
		rate := chargePlayerCount / int(active)

		basic := &BasiceStatitics{
			Timestamp: start,
			Keep1:     getKeep(start, 1),
			Keep3:     getKeep(start, 3),
			Keep7:     getKeep(start, 7),
			Keep30:    getKeep(start, 30),

			Active:          uint32(active),
			NewCount:        uint32(newuser),
			ARPU:            uint32(ARPU * 100),
			ActiveARPU:      uint32(activeARPU * 100),
			ExpenseRate:     uint32(rate * 100),
			ExpenseAll:      allMoney,
			ExpenseCount:    uint32(chargePlayerCount),
			ExpenseNewCount: uint32(newuserChargeCount),
		}
		m = append(m, basic)
		start += 86400
		if start > end {
			break
		}
	}
	return m
}

func getKeep(unix uint32, dayNum uint32) uint32 {
	unixstr := strconv.FormatInt(int64(unix), 10)
	lastDay := strconv.FormatInt(int64(unix-dayNum*86400), 10)
	activeList, err := gossdb.C().HgetAll(data.KEY_ACTIVE_STATISTICS + unixstr)
	if err != nil || len(activeList) == 0 {
		return 0
	}
	newusers, err := data.GetAllNewuser(lastDay)
	if err != nil || len(newusers) == 0 {
		return 0
	}
	var count int
	for _, v := range newusers {
		if _, ok := activeList[v.Userid]; ok {
			count++
		}
	}
	return uint32(count / len(newusers) * 100)
}
