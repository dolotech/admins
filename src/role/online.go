package role

import (
	"basic/ssdb/gossdb"
	"basic/utils"
	"data"
	"net/http"
	"strconv"

	"github.com/golang/glog"
	"github.com/labstack/echo"
)

//List 获取在线玩家列表
func ListOnline(c echo.Context) error {
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

	count, _ := gossdb.C().Hsize(data.KEY_ONLINE)
	list, _ := gossdb.C().Hkeys(data.KEY_ONLINE, "", "", int64(5000))

	ids := make([]string, 0, pageMax)
	for i := 0; i < pageMax; i++ {
		index := (page-1)*pageMax + i
		if index < int(count) {
			ids = append(ids, list[index].String())
		}
	}

	lists := data.GetMultiUser(ids)
	users := make([]*UserData, 0, len(lists))
	glog.Infoln(len(lists), lists)
	for _, v := range lists {
		u := &UserData{
			Userid:      v.Userid,
			Nickname:    v.Nickname,
			Phone:       v.Phone,
			Coin:        v.Coin,
			Diamond:     v.Diamond,
			Vip:         v.Vip,
			VipExpire:   v.VipExpire,
			Create_ip:   utils.InetTontoa(v.Create_ip).String(),
			Create_time: v.Create_time,
			Sex:         v.Sex,
			Ping:        v.Ping,
			Win:         v.Win,
			Lost:        v.Lost,
			Ticket:      v.Ticket,
			Exchange:    v.Exchange,
			Exp:         v.Exp,
		}
		users = append(users, u)
	}

	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": users, "count": count}})
}
