package csv

import (
	"basic/csv"
	"fmt"
	"io/ioutil"
	"os"

	"github.com/golang/glog"
)

var vipMap map[uint32]VipData

type VipData struct {
	ID           uint32 `csv:"id"`           //
	Viptype      uint32 `csv:"viptype"`      //
	Clubmax      uint32 `csv:"clubmax"`      //
	Deskmax      uint32 `csv:"deskmax"`      //
	Bankruptmax  uint32 `csv:"bankruptmax"`  //
	Bankruptcoin uint32 `csv:"bankruptcoin"` //
	Sign         uint32 `csv:"sign"`         //
	Expire       uint32 `csv:"expire"`       // VIP过期时间
}

func GetVip(id uint32) *VipData {
	vip, ok := vipMap[id]
	glog.Infoln(id, ok, vip)
	if ok {
		return &vip
	}
	return nil
}
func init() {
	f, err := os.Open("./csv/vip.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	var vip []VipData
	err = csv.Unmarshal(data, &vip)
	if err != nil {
		panic(err)
	}
	vipMap = make(map[uint32]VipData)
	for _, v := range vip {
		fmt.Println(v, v.Viptype)
		vipMap[v.ID] = v
	}
}
