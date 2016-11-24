package data

import (
	"basic/ssdb/gossdb"
	"basic/utils"
	"strconv"
	"time"

	"github.com/golang/glog"
)

// 新增用户
type NewUserStatistics struct {
	Unix   uint32
	Userid string
}
type NewUserCountStatistics struct {
	Unix  uint32
	Count uint32
}

func (this *NewUserStatistics) Save() error {
	timestamp := strconv.FormatInt(utils.TimestampToday(), 10)

	_, err := gossdb.C().Qpush_back(KEY_NEWUSER_STATISTICS+timestamp, this)
	if err != nil {
		return err
	}
	return nil
}
func GetNewUserStatitics(timestamp string) ([]*NewUserCountStatistics, error) {
	value, err := gossdb.C().Qslice(KEY_NEWUSER_STATISTICS+timestamp, 0, -1)
	glog.Infoln(err)
	//if err != nil {
	//	return nil, err
	//}

	//if len(value) <= 0 {
	//	return nil, errors.New("没有数据")
	//}
	list := make([]*NewUserCountStatistics, 0)
	today := uint32(utils.TimestampToday() + 300)
	now := uint32(time.Now().Unix() - 300)
	hash := make(map[int]*NewUserCountStatistics)

	var count int = 0
	for i := today; i < now; i += 300 {
		data := &NewUserCountStatistics{Unix: i}
		list = append(list, data)
		hash[count] = data
		glog.Infoln(count)
		count++
	}
	if value != nil && len(value) > 0 {
		for _, v := range value {
			newuser := &NewUserStatistics{}
			err := v.As(newuser)
			if err == nil {
				index := int((newuser.Unix - today) / 300)
				hash[index].Count++
			} else {
				glog.Errorln(err)
			}
		}
	}

	return list, nil
}
