package data

import (
	"basic/ssdb/gossdb"
	"basic/utils"
	"strconv"
	"time"

	"github.com/golang/glog"
)

type OnlineStatistics struct {
	Unix  uint32
	Count uint32
}

func GetOnlineStatitics(timestamp string) ([]*OnlineStatistics, error) {
	list := make([]*OnlineStatistics, 0)
	resp, err := gossdb.C().Do("qslice", KEY_ONLINE_STATISTICS + timestamp, 0, -1)
	if err != nil {
		return list, err
	}
	size := len(resp)
	if size >= 1 && resp[0].String() == "ok" {

		for i := 1; i < size; i++ {
			online := &OnlineStatistics{}
			if err := resp[i].As(online); err == nil {
				list = append(list, online)
			} else {
				glog.Errorln(err)
			}
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
		if err == nil && uint32(time.Now().Unix()) - data.Unix < 300 {
			return nil
		}
	}
	_, err = gossdb.C().Qpush_back(KEY_ONLINE_STATISTICS + timestamp, this)
	if err != nil {
		return err
	}
	return nil
}
