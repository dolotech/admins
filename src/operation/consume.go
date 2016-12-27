package operation

import (
	"data"
	"net/http"
	"github.com/labstack/echo"
	"github.com/golang/glog"

	"strconv"
	"basic/ssdb/gossdb"
)

//获取、扣除渠道
const (
	RESTYPE1 uint32 = iota //普通场抽成
	RESTYPE2               //普通场打牌
	RESTYPE3               //比赛场
	RESTYPE4               //私人局
	RESTYPE5               //破产
	RESTYPE6               //充值
	RESTYPE7               //签到
	RESTYPE8               //Vip
	RESTYPE9               //邮件
	RESTYPE10              //购买
	RESTYPE11              //兑换
	RESTYPE12              //排行榜
	RESTYPE13              //活动领奖
	RESTYPE14              //排行榜
	RESTYPE15              //任务
)


type ConsumeData struct {
	Userid   string //玩家ID
	Kind     uint32 //道具、货币种类
	Time     uint32 //变动时间
	Channel  uint32 //获取、扣除渠道
	Residual uint32 //剩余量
	Count 		int32 	// 变数量
}

// 玩家资源消耗日志
func Consume(c echo.Context) error {
	userid := c.FormValue("Userid")
	glog.Infoln("userid:",userid)
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


	size, err := gossdb.C().Qsize(data.KEY_RESOURCE_CHANGE + userid)
	if err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg":"no data"})
	}
	rang, err := gossdb.C().Qrange(data.KEY_RESOURCE_CHANGE+userid, (page - 1) * pageMax, pageMax)
	if err != nil {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg":"no data"})
	}

	list := make([]*ConsumeData, 0, len(rang))
	for _, v := range rang {

		data := &ConsumeData{}
		if err := v.As(data);err==nil{
			list = append(list, data)
		}
	}

	return c.JSON(http.StatusOK, data.H{"status": "ok", "count":size,"list": list})
}

