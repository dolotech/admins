package data

import (
	"basic/ssdb/gossdb"
	"time"

	"github.com/golang/glog"
)

// 进入游戏弹出的公告内容,
type Notice struct {
	Id      int64 // time.Now().Unix()
	Type    uint32
	Title   string
	Content string
	CTime   int64
	Expire  int64
}

func GetNotice() []*Notice {
	value, err := gossdb.C().Qrange(KEY_NOTICE, 0, 1)
	list := make([]*Notice, 0)
	now := time.Now().Unix()
	if err != nil {
		glog.Errorln("GetList get error:", err)
	}
	for _, v := range value {
		d := &Notice{}
		if err := v.As(d); err == nil {
			if d.Expire > now {
				list = append(list, d)
			}
		}
	}
	return list
}

func AddNotice(ntype uint32, expire int64, title, content string) {
	now := time.Now().Unix()
	n := &Notice{
		Id:      now,
		Type:    ntype,
		Title:   title,
		Content: content,
		CTime:   now,
		Expire:  expire,
	}
	_, err := gossdb.C().Qpush_front(KEY_NOTICE, n)
	if err != nil {
		glog.Errorln("Add notice error:", err)
	}
}
