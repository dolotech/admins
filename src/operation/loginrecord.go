package operation

import (
	"basic/iplocation"
	"basic/ssdb/gossdb"
	"basic/utils"
	"data"
	"net/http"
	"strconv"

	"github.com/golang/glog"
	"github.com/labstack/echo"
)

type DataUserActive struct {
	Userid   string //用户账号id
	Nickname string
	Address  string
	//Network  string //网络运营商
	IP     uint32
	Time   uint32 // 时间戳
	Action uint32 // 1:上线，2：下线
	Device string // 设备型号
}

func getLoginRecord(userid string, offset, limit int) ([]*DataUserActive, int64, error) {
	size, err := gossdb.C().Qsize(data.KEY_USER_ACTIVE + userid)
	if err != nil {
		return nil, size, err
	}
	rang, err := gossdb.C().Qrange(data.KEY_USER_ACTIVE+userid, offset, limit)
	if err != nil {
		return nil, size, err
	}
	glog.Infoln(len(rang), rang, userid, offset, limit)
	list := make([]*DataUserActive, len(rang))
	for i := 0; i < len(rang); i++ {
		item := &DataUserActive{}
		rang[i].As(item)
		ipdata := iplocation.Query(utils.InetTontoa(item.IP).String())
		glog.Infoln(*ipdata)
		if ipdata.Ok {
			item.Address = ipdata.Country
			//	item.Network = ipdata.Area
		}
		user := &data.User{Userid: item.Userid}
		if user.GetNickname() == nil {
			item.Nickname = user.Nickname
		}
		list[i] = item
	}
	return list, size, nil

}
func LoginRecord(c echo.Context) error {
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
	list, size, err := getLoginRecord(userid, ((page - 1) * pageMax), pageMax)
	if err != nil || size == 0 {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "列表为空"})
	}
	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": list, "count": size}})
}
