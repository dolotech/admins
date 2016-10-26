package role

import (
	"basic/ssdb/gossdb"
	"basic/utils"
	"data"
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"
	"github.com/golang/glog"
)

//List 获取在线玩家列表
func ListOnline(c *gin.Context) {
	page, _ := strconv.Atoi(c.PostForm("Page")) // string
	if page == 0 {
		page = 1
	}
	pageMax, _ := strconv.Atoi(c.PostForm("PageMax")) // string
	if pageMax == 0 {
		pageMax = 30
	}
	count, _ := gossdb.C().Hsize(data.KEY_ONLINE)
	list, _ := gossdb.C().Hkeys(data.KEY_ONLINE, "", "", int64(pageMax))
	ids := make([]string, len(list))
	for k, v := range list {
		ids[k] = v.String()
	}

	glog.Infoln(ids)
	//	lastID, _ := gossdb.C().Get(data.KEY_LAST_USER_ID)
	//	idnum, err := strconv.ParseUint(lastID.String(), 10, 64)
	//	if err == nil && idnum > 60000 {
	//		count = idnum - 60000
	//	}
	//	end := idnum - uint64(pageMax*(page-1))
	//	start := idnum - uint64(pageMax*page)
	//	var i uint64
	//	for i = end; i > start; i-- {
	//		ids = append(ids, strconv.FormatUint(i, 10))
	//	}
	//
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
			Create_ip:   utils.InetTontoa(v.Create_ip).String(),
			Create_time: utils.Unix2Str(int64(v.Create_time)),
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

	data := make(map[string]interface{})
	data["list"] = users
	data["count"] = count
	c.JSON(http.StatusOK, gin.H{"status": "ok", "data": data})
}
