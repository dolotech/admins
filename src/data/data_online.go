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
	//size, _ := gossdb.C().Qsize(KEY_ONLINE_STATISTICS + timestamp)
	list := make([]*OnlineStatistics, 0)
	//value, err := gossdb.C().Qrange(KEY_ONLINE_STATISTICS+timestamp, 0, int(size))

	//	resp, err := gossdb.C().Do("qslice", KEY_ONLINE_STATISTICS+timestamp, 0, -1)
	//	if err != nil {
	//		return list, err
	//	}
	//	size := len(resp)
	//	if size >= 1 && resp[0].String() == "ok" {
	//		glog.Infoln("在线数据长度:", size-1)
	//		for i := 1; i < size; i++ {
	//			online := &OnlineStatistics{}
	//			if err := resp[i].As(online); err == nil {
	//				list = append(list, online)
	//				gossdb.C().Hset("mytest:", strconv.FormatInt(int64(online.Unix), 10), online)
	//			} else {
	//				glog.Errorln(err)
	//
	//			}
	//		}
	//	}

	hashaMap, err := gossdb.C().MultiHgetAll("mytest:")
	glog.Infoln("len(hashaMap):", len(hashaMap), err)
	size, err := gossdb.C().Hsize("mytest:")

	glog.Infoln("size:", size, err)
	//if size >= 1 && resp[0][0] == ok[0] && resp[0][1] == ok[1] {
	//		for i := 1; i < size; i++ {
	//			v = append(v, resp[i])
	//		}
	//		return
	//	}
	//
	//	if len(value) <= 0 {
	//		return nil, errors.New("没有数据")
	//	}
	//glog.Infoln("在线数据长度:", len(value))
	//for _, v := range value {
	//	online := &OnlineStatistics{}
	//	err := v.As(online)
	//	if err == nil {
	//		list = append(list, online)
	//	} else {
	//		glog.Errorln(err)
	//	}
	//}
	//glog.Infoln("在线数据长度:", len(list))
	//count, _ := gossdb.C().Qsize(KEY_ONLINE)
	//data := &OnlineStatistics{Unix: uint32(time.Now().Unix()), Count: uint32(count)}
	//list = append(list, data)

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
