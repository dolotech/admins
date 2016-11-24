package data

import (
	"basic/ssdb/gossdb"
	"strconv"

	"github.com/golang/glog"
)

// 比赛场牌局记录，用于后台系统
type GameRecordMatch struct {
	Userid        string
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
	Score         int32  //
	Tingvalue     uint32 //
	Hutype        uint32 //
	Huvalue       uint32 //
	Create_time   uint32 //
	OutCards      []byte
	CardRecord    string
	StartHandCard []byte // 起手牌
}

func (this *GameRecordMatch) Save() error {
	err := gossdb.C().Hset(KEY_MATCH_GAME_RECORD+this.Userid, strconv.Itoa(int(this.Create_time)), this)
	if err != nil {
		return err
	}
	_, err = gossdb.C().Qpush_front(KEY_MATCH_GAME_RECORD_QUEUE+this.Userid, this.Create_time)
	if err != nil {
		return err
	}
	return nil
}

// 私人局牌局记录，用于后台系统
type GameRecordPrivate struct {
	Userid    string
	Zhuang    uint32 //
	Seat      uint32 //
	Paoseat   uint32 //
	Ante      uint32 //
	Ji        byte   //
	HeroJi    uint32 // 0:无，1:英雄鸡，2：责任鸡，3：责任鸡碰方
	HuCard    byte
	Handcard  []byte
	Peng      []uint32
	Kong      []uint32
	Otherids  []string
	Rtype     uint32 //
	Score     int32  //
	Tingvalue uint32 //
	Hutype    uint32 //
	Huvalue   uint32 //

	OutCards      []byte
	CardRecord    string
	StartHandCard []byte // 起手牌

	Create_userid string //  房主
	CTime         uint32 // 房间创建时间
	Invitecode    string
	Expire        uint32
	Payment       uint32 //付费方式1=AA or 0=房主支付
	Updownji      uint32 //是否有上下鸡
	Rname         string
	Round         uint32 //剩余牌局数
	RoundTotal    uint32 //总牌局数
	Create_time   uint32 //记录创建时间
}

func (this *GameRecordPrivate) Save() error {
	err := gossdb.C().Hset(KEY_PRIVATE_GAME_RECORD+this.Userid, strconv.Itoa(int(this.Create_time)), this)
	if err != nil {
		return err
	}
	_, err = gossdb.C().Qpush_front(KEY_PRIVATE_GAME_RECORD_QUEUE+this.Userid, this.Create_time)
	if err != nil {
		return err
	}
	return nil
}

// 金币场牌局记录，用于后台系统
type GameRecord struct {
	Userid        string
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

func (this *GameRecord) Save() error {
	err := gossdb.C().Hset(KEY_GAME_RECORD+this.Userid, strconv.Itoa(int(this.Create_time)), this)
	if err != nil {
		return err
	}
	_, err = gossdb.C().Qpush_front(KEY_GAME_RECORD_QUEUE+this.Userid, this.Create_time)
	if err != nil {
		return err
	}
	return nil
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
		err = value.As(data)
		if err == nil {
			list = append(list, data)
		}
	}
	return list, nil
} //  获取金币场牌局记录
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
		err = value.As(data)
		if err == nil {
			list = append(list, data)
		}
	}
	return list, size, nil
}

//-----------------------------------------私人局------------------------------------------------------------
// 获取同桌数据
func GetPrivateDestopRecord(userid string, createTime string) ([]*GameRecordPrivate, error) {
	value, err := gossdb.C().Hget(KEY_PRIVATE_GAME_RECORD+userid, createTime)
	if err != nil {
		return nil, err
	}
	record := &GameRecordPrivate{}
	value.As(record)

	list := make([]*GameRecordPrivate, 0, len(record.Otherids))
	for _, v := range record.Otherids {
		value, err := gossdb.C().Hget(KEY_PRIVATE_GAME_RECORD+v, createTime)
		if err != nil {
			continue
		}
		data := &GameRecordPrivate{}
		err = value.As(data)
		if err == nil {
			list = append(list, data)
		}
	}
	glog.Infoln(list)
	return list, nil
}
func GetPrivateRecord(userid string, offset, limit int) ([]*GameRecordPrivate, int64, error) {
	size, err := gossdb.C().Qsize(KEY_PRIVATE_GAME_RECORD_QUEUE + userid)
	if err != nil {
		return nil, size, err
	}
	rang, err := gossdb.C().Qrange(KEY_PRIVATE_GAME_RECORD_QUEUE+userid, offset, limit)
	if err != nil {
		return nil, size, err
	}

	list := make([]*GameRecordPrivate, 0, len(rang))
	for _, v := range rang {
		value, err := gossdb.C().Hget(KEY_PRIVATE_GAME_RECORD+userid, v.String())
		if err != nil {
			continue
		}

		data := &GameRecordPrivate{}
		err = value.As(data)
		if err == nil {
			list = append(list, data)
		}
	}
	return list, size, nil
}

//-----------------------------------------比赛场------------------------------------------------------------
// 获取同桌数据
func GetMatchDestopRecord(userid string, createTime string) ([]*GameRecordMatch, error) {
	value, err := gossdb.C().Hget(KEY_MATCH_GAME_RECORD+userid, createTime)
	if err != nil {
		return nil, err
	}
	record := &GameRecordMatch{}
	value.As(record)

	list := make([]*GameRecordMatch, 0, len(record.Otherids))
	for _, v := range record.Otherids {
		value, err := gossdb.C().Hget(KEY_MATCH_GAME_RECORD+v, createTime)
		if err != nil {
			continue
		}
		data := &GameRecordMatch{}
		err = value.As(data)
		if err == nil {
			list = append(list, data)
		}
	}
	glog.Infoln(list)
	return list, nil
}
func GetMatchRecord(userid string, offset, limit int) ([]*GameRecordMatch, int64, error) {
	size, err := gossdb.C().Qsize(KEY_MATCH_GAME_RECORD_QUEUE + userid)
	if err != nil {
		return nil, size, err
	}
	rang, err := gossdb.C().Qrange(KEY_MATCH_GAME_RECORD_QUEUE+userid, offset, limit)
	if err != nil {
		return nil, size, err
	}

	list := make([]*GameRecordMatch, 0, len(rang))
	for _, v := range rang {
		value, err := gossdb.C().Hget(KEY_MATCH_GAME_RECORD+userid, v.String())
		if err != nil {
			continue
		}

		data := &GameRecordMatch{}
		err = value.As(data)
		if err == nil {
			list = append(list, data)
		}
	}
	glog.Infoln(list, size)
	return list, size, nil
}
