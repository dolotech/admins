package data

import (
	"basic/ssdb/gossdb"

	"github.com/golang/glog"
)

type NewUserStatistics struct {
	Unix   uint32
	Userid string
}
type NewUserCountStatistics struct {
	Unix  uint32
	Count uint32
}

// 获取当日所有新增用户数据
func GetAllNewuser(timestamp string) ([]*NewUserStatistics, error) {
	value, err := gossdb.C().MultiHgetAll(KEY_NEWUSER_STATISTICS + timestamp)
	if err != nil {
		return nil, err
	}
	list := make([]*NewUserStatistics, 0, len(value))
	for k, v := range value {
		data := &NewUserStatistics{Userid: k, Unix: v.UInt32()}
		list = append(list, data)
	}
	return list, nil
}

func GetNewUserStatitics(timestamp string) ([]*NewUserCountStatistics, error) {
	value, err := gossdb.C().Qslice(KEY_NEWUSER_STATISTICS_QUE+timestamp, 0, -1)
	glog.Infoln(err)

	list := make([]*NewUserCountStatistics, 0, len(value))
	for _, v := range value {
		data := &NewUserCountStatistics{}
		if err := v.As(data); err == nil {
			list = append(list, data)
		}
	}
	//	count, _ := gossdb.C().Qsize(KEY_NEWUSER_STATISTICS)
	//	data := &NewUserCountStatistics{Unix: uint32(time.Now().Unix()), Count: uint32(count)}
	//	list = append(list, data)
	return list, nil
}
