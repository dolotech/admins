/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-03-18 10:16
 * Filename      : roles.go
 * Description   :
 * *******************************************************/
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

const (
	LIMIT int = 30
)

var OPTION = map[string]string{
	"30":  "30",
	"50":  "50",
	"100": "100",
	"200": "200",
}

type UserData struct {
	Userid      string // 用户id
	Nickname    string // 用户昵称
	Phone       string // 绑定的手机号码
	Coin        uint32 // 金币
	Diamond     uint32 // 钻石
	Vip         uint32 // Vip
	VipExpire   uint32 // Vip 过期时间
	Create_ip   string // 注册账户时的IP地址
	Create_time uint32 // 注册时间
	Sex         uint32
	Ticket      uint32 //入场券
	Exchange    uint32 //兑换券
	Exp         uint32 // 经验
	Win         uint32
	Lost        uint32
	Ping        uint32
	Photo       string
}

// 玩家信息编辑
func Edit(c echo.Context) error {
	userid := c.FormValue("Userid")
	nickname := c.FormValue("Nickname")
	sex := c.FormValue("Sex")
	phone := c.FormValue("Phone")
	vip := c.FormValue("Vip")
	coin := c.FormValue("Coin")
	diamond := c.FormValue("Diamond")
	exp := c.FormValue("Exp")
	ticket := c.FormValue("Ticket")
	exchange := c.FormValue("Exchange")
	win := c.FormValue("Win")
	lost := c.FormValue("Lost")
	ping := c.FormValue("Ping")
	pwd := c.FormValue("Password")
	pwd1 := c.FormValue("Password1")
	photo := c.FormValue("Photo")
	user := &data.User{Userid: userid}
	m := make(map[string]interface{})
	if nickname != "" {
		m["Nickname"] = nickname
	}
	if sex == "1" {
		m["Sex"] = 1
	} else {
		m["Sex"] = 2
	}
	if win != "" {
		m["Win"], _ = strconv.Atoi(win)
	}
	if photo != "" {
		m["Photo"], _ = strconv.Atoi(photo)
	}
	if lost != "" {
		m["Lost"], _ = strconv.Atoi(lost)
	}
	if ping != "" {
		m["Ping"], _ = strconv.Atoi(ping)
	}
	if ticket != "" {
		m["Ticket"], _ = strconv.Atoi(ticket)
	}
	if exchange != "" {
		m["Exchange"], _ = strconv.Atoi(exchange)
	}

	if exp != "" {
		m["Exp"], _ = strconv.Atoi(exp)
	}
	if vip != "" {
		m["Vip"], _ = strconv.Atoi(vip)
	}
	if coin != "" {
		m["Coin"], _ = strconv.Atoi(coin)
	}
	if diamond != "" {
		m["Diamond"], _ = strconv.Atoi(diamond)

	}
	if phone != "" {
		m["Phone"] = phone
	}
	if pwd != "" && pwd == pwd1 {
		user.UpdatePWD(pwd)
	}
	glog.Infoln(m, pwd, pwd1)
	user.MultiHsetSave(m)
	return c.JSON(http.StatusOK, data.H{"status": "ok", "msg": "玩家数据修改成功"})
}

// 玩家列表, 根据条件检索玩家
func Search(c echo.Context) error {
	searchType := c.FormValue("SelectedIDSearch")
	searchValue := c.FormValue("SearchUserid")
	page, _ := strconv.Atoi(c.FormValue("Page")) // string
	if page == 0 {
		page = 1
	}
	pageMax, _ := strconv.Atoi(c.FormValue("PageMax")) // string
	if pageMax == 0 {
		pageMax = 30
	}

	var ids []string
	var count uint64 = 0
	if searchValue != "" {
		if searchType == "1" {
			ids = append(ids, searchValue)
		} else if searchType == "2" {
			glog.Infoln(searchValue)
			glog.Infoln(utils.PhoneRegexp(searchValue))
			if utils.PhoneRegexp(searchValue) {
				value, err := gossdb.C().Hget(data.KEY_PHONE_INDEX, searchValue)
				if err == nil && len(value) > 0 {
					ids = append(ids, string(value))
				}
			}
			glog.Infoln(ids)
		}
		count = uint64(len(ids))
	} else {
		return c.JSON(http.StatusOK, data.H{"status": "fail", "msg": "请输入搜索的内容"})
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

//List 玩家列表, 根据条件检索玩家
func List(c echo.Context) error {
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

	users := make([]*UserData, 0, pageMax)
	var count uint64

	var ids []string
	lastID, err := gossdb.C().Get(data.KEY_LAST_USER_ID)
	if err != nil {
		glog.Errorln(err)
		return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": users, "count": count}})
	}

	idnum, _ := strconv.ParseUint(lastID.String(), 10, 64)
	if idnum > 0 {
		idnum--
	}
	if idnum > 60000 {
		count = idnum - 60000
	} else {
		glog.Errorln(idnum)
		return c.JSON(http.StatusOK, data.H{"status": "ok", "data": data.H{"list": users, "count": count}})
	}

	end := idnum - uint64(pageMax*(page-1))
	start := idnum - uint64(pageMax*page)
	var i uint64
	for i = end; i > start; i-- {
		ids = append(ids, strconv.FormatUint(i, 10))
	}

	lists := data.GetMultiUser(ids)
	glog.Infoln(len(lists))
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
