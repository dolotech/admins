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
	"fmt"
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

var Roles roles = roles{}

type roles struct {
	Userid      string // 用户id
	Nickname    string // 用户昵称
	PhoneN      string // 绑定的手机号码
	Coin        uint32 // 金币
	Diamond     uint32 // 钻石
	Vip         uint32 // Vip
	Create_ip   string // 注册账户时的IP地址
	Create_time string // 注册时间
}

// 玩家信息编辑
func (r *roles) Edit(c *gin.Context) {
	userid := c.Query("userid")
	nickname := c.Query("nickname")
	sex := c.Query("sex")
	phone := c.Query("phone")
	vip := c.Query("vip")
	coin := c.Query("coin")
	diamond := c.Query("diamond")
	glog.Infoln(userid, nickname, sex, phone, vip, coin, diamond)
}

// 用户
func (r *roles) List(c *gin.Context) {
	start_id := c.Query("start_id")
	end_id := c.Query("end_id")
	page_s := c.Query("page") // string
	act_s := c.Query("act")   // string
	limit_s := c.Query("limit")
	fmt.Println("start_id:", start_id, "end_id:", end_id)
	Pager.SetPager(page_s, limit_s, act_s)
	lastID, err := gossdb.C().Get(data.KEY_LAST_USER_ID)
	id := lastID.Int()
	if err != nil {
		fmt.Println("List Error:", err)
	}
	var ids []string
	p := Pager.Page * Pager.Limit
	s := (Pager.Page-1)*Pager.Limit + 1
	for ; s <= p; s++ {
		ids = append(ids, strconv.Itoa(int(id-s)))
	}
	// fmt.Println("ids:", ids)
	Selected.SetSelect("limit", limit_s, OPTION)
	lists := data.GetMultiUser(ids)
	users := make([]*roles, 0, len(lists))
	for _, v := range lists {
		u := &roles{
			Userid:      v.Userid,
			Nickname:    v.Nickname,
			PhoneN:      v.Phone,
			Coin:        v.Coin,
			Diamond:     v.Diamond,
			Vip:         v.Vip,
			Create_ip:   utils.InetTontoa(v.Create_ip).String(),
			Create_time: utils.Unix2Str(int64(v.Create_time)),
		}
		users = append(users, u)
	}
	// fmt.Println("users : ", users)
	Pager.SetSize(uint32(LIMIT))
	fmt.Println("Pager", Pager)
	c.HTML(http.StatusOK, "lists.html", gin.H{
		"pager":    Pager,
		"selected": Selected,
		"users":    users,
	})
}

// 手机
func (r *roles) Phone(c *gin.Context) {
	start_id_s := c.Query("start_id")
	end_id_s := c.Query("end_id")
	page_s := c.Query("page") // string
	act_s := c.Query("act")   // string
	limit_s := c.Query("limit")
	Pager.SetPager(page_s, limit_s, act_s)
	// 数据库超时处理
	// value, err := gossdb.C().Hsize(data.KEY_PHONE_INDEX)
	// if err != nil {
	// 	value = 0
	// }
	phones, err := gossdb.C().Hscan(data.KEY_PHONE_INDEX, start_id_s, end_id_s, int64(Pager.Limit))
	if err != nil {
		phones = make(map[string]gossdb.Value)
	}
	Pager.SetSize(uint32(len(phones)))
	Selected.SetSelect("limit", limit_s, OPTION)
	fmt.Println("Pager", Pager, "Selected:", Selected, "limit_s:", limit_s)
	c.HTML(http.StatusOK, "phone.html", gin.H{
		"selected": Selected,
		"pager":    Pager,
		"start_id": start_id_s,
		"end_id":   end_id_s,
		"phones":   phones,
	})
}
