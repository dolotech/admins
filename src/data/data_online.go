package data

import (
	"basic/ssdb/gossdb"
	"basic/utils"
	"errors"
	"strconv"
	"time"

	"github.com/golang/glog"
)

type OnlineStatistics struct {
	Unix  uint32
	Count uint32
}

func GetOnlineStatitics(timestamp string) ([]*OnlineStatistics, error) {
	value, err := gossdb.C().Qslice(KEY_ONLINE_STATISTICS+timestamp, 0, -1)
	if err != nil {
		return nil, err
	}

	if len(value) <= 0 {
		return nil, errors.New("没有数据")
	}
	list := make([]*OnlineStatistics, 0, len(value))
	for _, v := range value {
		online := &OnlineStatistics{}
		err := v.As(online)
		if err == nil {
			list = append(list, online)
		} else {
			glog.Errorln(err)
		}
	}
	return list, nil
}
func (this *OnlineStatistics) Save() error {
	defer func() {
		if err := recover(); err != nil {
			glog.Errorln(err)
		}
	}()

	timestamp := strconv.FormatInt(utils.TimestampToday(), 10)

	value, err := gossdb.C().Qback(KEY_ONLINE_STATISTICS + timestamp)
	if err == nil {
		data := &OnlineStatistics{}
		err := value.As(data)
		if err == nil && uint32(time.Now().Unix())-data.Unix < 300 {
			return nil
		}
	}
	glog.Infoln("在线人数统计: ", this)
	_, err = gossdb.C().Qpush_back(KEY_ONLINE_STATISTICS+timestamp, this)
	if err != nil {
		return err
	}
	return nil
}
