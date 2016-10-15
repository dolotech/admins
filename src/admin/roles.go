/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-03-18 10:16
 * Filename      : roles.go
 * Description   :
 * *******************************************************/
package admin

import (
	"basic/ssdb/gossdb"
	"basic/utils"
	"data"
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"
	"github.com/golang/glog"
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

var Roles = roles{}

type UserData struct {
	Userid      string // 用户id
	Nickname    string // 用户昵称
	Phone       string // 绑定的手机号码
	Coin        uint32 // 金币
	Diamond     uint32 // 钻石
	Vip         uint32 // Vip
	Create_ip   string // 注册账户时的IP地址
	Create_time string // 注册时间
	Sex         uint32
	Ticket      uint32 //入场券
	Exchange    uint32 //兑换券
	Exp         uint32 // 经验
	Win         uint32
	Lost        uint32
	Ping        uint32
	Photo       string
}
type roles struct {
	pager    pager
	selector selected
}

// 玩家信息编辑
func (this *roles) Edit(c *gin.Context) {
	userid := c.PostForm("Userid")
	nickname := c.PostForm("Nickname")
	sex := c.PostForm("Sex")
	phone := c.PostForm("Phone")
	vip := c.PostForm("Vip")
	coin := c.PostForm("Coin")
	diamond := c.PostForm("Diamond")
	exp := c.PostForm("Exp")
	ticket := c.PostForm("Ticket")
	exchange := c.PostForm("Exchange")
	win := c.PostForm("Win")
	lost := c.PostForm("Lost")
	ping := c.PostForm("Ping")
	pwd := c.PostForm("Password")
	pwd1 := c.PostForm("Password1")
	photo := c.PostForm("Photo")
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
	c.JSON(http.StatusOK, gin.H{"status": "ok", "msg": "玩家数据修改成功"})
}

// 玩家列表, 根据条件检索玩家
func (this *roles) List(c *gin.Context) {
	searchType := c.PostForm("SelectedIDSearch")
	searchValue := c.PostForm("SearchUserid")
	page_s := c.PostForm("page") // string
	act_s := c.PostForm("act")   // string
	limit_s := c.PostForm("limit")

	this.pager.SetPager(page_s, limit_s, act_s)

	this.selector.SetSelect("limit", limit_s, OPTION)

	var ids []string
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
	} else {
		lastID, _ := gossdb.C().Get(data.KEY_LAST_USER_ID)
		idnum, err := strconv.ParseUint(lastID.String(), 10, 64)
		if err == nil && idnum > 60001 {
			size := idnum - 60001
			glog.Infoln("size ", size)
			this.pager.SetSize(uint32(size))
		}
		glog.Infoln(this.pager.GetStart(), this.pager.GetEnd())
		ids = utils.Between(strconv.FormatUint(idnum-uint64(this.pager.GetEnd()), 10), strconv.FormatUint(idnum-uint64(this.pager.GetStart()), 10))

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
	glog.Infoln("users : ", this.pager, len(users))

	c.JSON(http.StatusOK, gin.H{"status": "ok", "data": users})
}
