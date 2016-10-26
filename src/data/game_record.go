package data

import (
	"basic/ssdb/gossdb"
	"inter"
	"strconv"

	"github.com/golang/glog"
)

type GameRecord struct {
	Userid      string
	Zhuang      uint32 //
	Seat        uint32 //
	Paoseat     uint32 //
	Ante        uint32 //
	Ji          byte   //
	Handcard    []byte
	Peng        []inter.IPeng
	Kong        []inter.IKong //
	Otherids    []string
	Rtype       uint32 //
	Coin        int32  //
	Tingvalue   uint32 //
	Hutype      uint32 //
	Huvalue     uint32 //
	Create_time uint32 //
}

//  获取金币场牌局记录
func GetNormalRecord(userid string, startKey string, endKey string, limit int64) ([]*GameRecord, error) {
	value, err := gossdb.C().Hrscan(KEY_GAME_RECORD+":"+userid, startKey, endKey, limit)
	if err != nil {
		return nil, err
	}
	list := make([]*GameRecord, 0, len(value))
	for _, v := range value {
		data := &GameRecord{}
		glog.Errorln(v)
		v.As(data)
		list = append(list, data)
	}
	return list, nil
}
func (this *GameRecord) Save() error {
	err := gossdb.C().Hset(KEY_GAME_RECORD+":"+this.Userid, strconv.Itoa(int(this.Create_time)), this)
	if err != nil {
		return err
	}
	return nil
}
