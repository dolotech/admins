package data

import (
	"basic/ssdb/gossdb"
	"strconv"
)

type GameRecord struct {
	Userid string

	Zhuang        uint32 //
	Seat          uint32 //
	Paoseat       uint32 //
	Ante          uint32 //
	Ji            byte   //
	HeroJi        uint32 // 0:无，1:英雄鸡，2：责任鸡，3：责任鸡碰方
	HuCard        byte
	Handcard      []byte
	Peng          []uint32
	Kong          []uint32
	Otherids      []string
	Rtype         uint32 //
	Coin          int32  //
	Tingvalue     uint32 //
	Hutype        uint32 //
	Huvalue       uint32 //
	Create_time   uint32 //
	OutCards      []byte
	CardRecord    string
	StartHandCard []byte // 起手牌
}

// 获取同桌数据
func GetDestopRecord(userid string, createTime string) ([]*GameRecord, error) {
	value, err := gossdb.C().Hget(KEY_GAME_RECORD+userid, createTime)
	if err != nil {
		return nil, err
	}
	record := &GameRecord{}
	value.As(record)

	list := make([]*GameRecord, 0, len(record.Otherids))
	for _, v := range record.Otherids {
		value, err := gossdb.C().Hget(KEY_GAME_RECORD+v, createTime)
		if err != nil {
			continue
		}
		data := &GameRecord{}
		value.As(data)
		list = append(list, data)
	}
	return list, nil
}

//  获取金币场牌局记录
func GetNormalRecord(userid string, offset, limit int) ([]*GameRecord, int64, error) {
	size, err := gossdb.C().Qsize(KEY_GAME_RECORD_QUEUE + userid)
	if err != nil {
		return nil, size, err
	}
	rang, err := gossdb.C().Qrange(KEY_GAME_RECORD_QUEUE+userid, offset, limit)
	if err != nil {
		return nil, size, err
	}

	list := make([]*GameRecord, 0, len(rang))
	for _, v := range rang {
		value, err := gossdb.C().Hget(KEY_GAME_RECORD+userid, v.String())
		if err != nil {
			continue
		}

		data := &GameRecord{}
		value.As(data)
		list = append(list, data)
	}
	return list, size, nil
}
func (this *GameRecord) Save() error {
	err := gossdb.C().Hset(KEY_GAME_RECORD+this.Userid, strconv.Itoa(int(this.Create_time)), this)
	if err != nil {
		return err
	}
	return nil
}
