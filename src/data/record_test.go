package data

import (
	"basic/ssdb/gossdb"
	"testing"

	"github.com/golang/glog"
)

func Test_normal_record(t *testing.T) {
	gossdb.Connect("119.29.24.17", 8888, 1)
	list, _ := GetNormalRecord("65168", "", "", 100)
	for _, v := range list {
		glog.Errorln(*v)
	}
}
