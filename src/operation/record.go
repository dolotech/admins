package operation

import (
	"data"
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"
)

type Record struct {
	Userid      string
	Zhuang      uint32 //
	Seat        uint32 //
	Paoseat     uint32 //
	Ante        uint32 //
	Ji          uint32 //
	HeroJi      uint32 // 0:无，1:英雄鸡，2：责任鸡，3：责任鸡碰方
	Handcard    []uint32
	Peng        []uint32
	Kong        []uint32 //
	HuCard      uint32
	Otherids    []string
	Rtype       uint32 //
	Coin        int32  //
	Tingvalue   uint32 //
	Hutype      uint32 //
	Huvalue     uint32 //
	Create_time uint32 //
}

// 获取金币场牌局记录
func NormalRecord(c *gin.Context) {
	page, _ := strconv.Atoi(c.PostForm("Page")) // string
	if page == 0 {
		page = 1
	}
	pageMax, _ := strconv.Atoi(c.PostForm("PageMax")) // string
	if pageMax == 0 {
		pageMax = 30
	}
	userid := c.PostForm("Userid")
	createTime := c.PostForm("Create_time")
	var list []*data.GameRecord
	var count int64
	if createTime == "" {
		list, count, _ = data.GetNormalRecord(userid, ((page - 1) * pageMax), pageMax)
	} else {
		list, _ = data.GetDestopRecord(userid, createTime)
		count = 4
	}
	users := make([]*Record, 0, len(list))
	for _, v := range list {
		u := &Record{
			Userid:      v.Userid,
			Zhuang:      v.Zhuang,
			Seat:        v.Seat,
			Paoseat:     v.Paoseat,
			Ante:        v.Ante,
			Ji:          uint32(v.Ji),
			HuCard:      uint32(v.HuCard),
			Peng:        v.Peng,
			Kong:        v.Kong,
			Rtype:       v.Rtype,
			Coin:        v.Coin,
			Tingvalue:   v.Tingvalue,
			Hutype:      v.Hutype,
			Huvalue:     v.Huvalue,
			Create_time: v.Create_time,
			Otherids:    v.Otherids,
			HeroJi:      v.HeroJi,
		}
		for i := 0; i < len(v.Handcard); i++ {
			u.Handcard = append(u.Handcard, uint32(v.Handcard[i]))
		}
		users = append(users, u)
	}

	data := make(map[string]interface{})
	data["list"] = users
	data["count"] = count
	c.JSON(http.StatusOK, gin.H{"status": "ok", "data": data})
}
