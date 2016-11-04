package csv

import (
	"basic/csv"
	"io/ioutil"
	"os"

	"github.com/golang/glog"
)

var socialMap map[uint32]DataSocial

type DataSocial struct {
	Typeid    uint32 `csv:"typeid"`
	Maxcount  uint32 `csv:"maxcount"`
	Cost      uint32 `csv:"cost"`
	Costcount uint32 `csv:"costcount"`
	Seniority uint32 `csv:"seniority "`
}

var list []DataSocial

func GetSocialList() []DataSocial {
	return list
}
func GetSocial(id uint32) *DataSocial {
	task := socialMap[id]
	return &task
}
func init() {
	f, err := os.Open("./csv/social.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	err = csv.Unmarshal(data, &list)
	if err != nil {
		panic(err)
	}
	socialMap = make(map[uint32]DataSocial)
	for _, v := range list {
		socialMap[v.Typeid] = v
	}
	glog.Infoln("圈子表：", len(socialMap))
}
