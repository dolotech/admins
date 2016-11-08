package operation

import (
	"basic/ssdb/gossdb"
	"data"
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"
	"github.com/golang/glog"
)

type DataRoom struct {
	Id       uint32 //房间ID
	Rtype    uint32 //房间类型
	Rname    string //房间名字
	Cid      uint32 //圈子id
	Expire   uint32 //牌局设定的过期时间
	Updownji uint32 //是否有上下鸡
	//	Pub           uint32 //是否同圈公开
	Invitecode    string //房间邀请码
	Create_userid string //房间创建人
	Status        uint32 //
	//	Round         uint32 //剩余牌局数

	RoundTotal uint32 // 总牌局数
	Started    bool   //牌局是否已经开始
	Ante       uint32 //私人房底分
	Payment    uint32 //付费方式1=AA or 0=房主支付
	//	Destroyer  string            //发请投票者
	//	Destroy    map[string]uint32 //投票解散0同意,1不同意
	CTime uint32 //创建时间
	//Score      map[string]int32  //私人局用户战绩积分
}

func getRoomCreateRecord(userid string, offset, limit int) ([]*DataRoom, int64, error) {
	size, err := gossdb.C().Qsize(data.KEY_ROOM_USER_CREATE_RECORD + userid)
	if err != nil {
		return nil, size, err
	}
	rang, err := gossdb.C().Qrange(data.KEY_ROOM_USER_CREATE_RECORD+userid, offset, limit)
	if err != nil {
		return nil, size, err
	}
	glog.Infoln(len(rang), rang, userid, offset, limit)
	list := make([]*DataRoom, len(rang))
	for i := 0; i < len(rang); i++ {
		item := &DataRoom{}
		rang[i].As(item)
		list[i] = item
	}
	return list, size, nil

}
func RoomCreateRecord(c *gin.Context) {
	page, _ := strconv.Atoi(c.PostForm("Page")) // string
	if page < 1 {
		page = 1
	}
	pageMax, _ := strconv.Atoi(c.PostForm("PageMax")) // string
	if pageMax < 30 {
		pageMax = 30
	} else if pageMax > 200 {
		pageMax = 200
	}
	userid := c.PostForm("Userid")
	list, size, err := getRoomCreateRecord(userid, ((page - 1) * pageMax), pageMax)
	if err != nil || size == 0 {
		c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "列表为空"})
		return
	}
	data := make(map[string]interface{})
	data["list"] = list
	data["count"] = size
	c.JSON(http.StatusOK, gin.H{"status": "ok", "data": data})
}
