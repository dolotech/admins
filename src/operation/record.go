package operation

import (
	"basic/ssdb/gossdb"
	"basic/utils"
	"data"
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"
)

type Record struct {
	Userid   string
	Zhuang   uint32 //
	Seat     uint32 //
	Paoseat  uint32 //
	Ante     uint32 //
	Ji       byte   //
	Handcard []uint32
	//Peng        []uint32
	//	Kong        []uint32 //
	Peng []uint32
	Kong []uint32 //

	Otherids    []string
	Rtype       uint32 //
	Coin        int32  //
	Tingvalue   uint32 //
	Hutype      uint32 //
	Huvalue     uint32 //
	Create_time string //
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
	list, _ := data.GetNormalRecord(userid, "", "", 100)
	count, _ := gossdb.C().Hsize(data.KEY_GAME_RECORD + ":" + userid)
	users := make([]*Record, 0, len(list))
	for _, v := range list {
		u := &Record{
			Userid:  v.Userid,
			Zhuang:  v.Zhuang,
			Seat:    v.Seat,
			Paoseat: v.Paoseat,
			Ante:    v.Ante,
			Ji:      v.Ji,
			Peng:    v.Peng,
			Kong:    v.Kong,
			//			Handcard:    v.Handcard,
			//	Otherids:    v.Otherids,
			Rtype:       v.Rtype,
			Coin:        v.Coin,
			Tingvalue:   v.Tingvalue,
			Hutype:      v.Hutype,
			Huvalue:     v.Huvalue,
			Create_time: utils.Unix2Str(int64(v.Create_time)),
		}
		//for i := 0; i < len(v.Kong); i++ {
		//	u.Kong = append(u.Kong, uint32(v.Kong[i].GetCard()))
		//}
		//for i := 0; i < len(v.Peng); i++ {
		//	u.Peng = append(u.Peng, uint32(v.Peng[i].GetCard()))
		//}
		for i := 0; i < len(v.Otherids); i++ {
			if v.Otherids[i] != userid {
				u.Otherids = append(u.Otherids, v.Otherids[i])

			}
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
