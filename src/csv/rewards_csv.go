package csv

import (
	"basic/csv"
	"io/ioutil"
	"os"

	"github.com/golang/glog"
)

var rewardsMap map[uint32][]RewardsData

type RewardsData struct {
	Typeid   uint32 `csv:"typeid"`
	Widgetid uint32 `csv:"widgetid"`
	Num      uint32 `csv:"num"`
}

func GetRewards(id uint32) []RewardsData {
	return rewardsMap[id]
}
func init() {
	f, err := os.Open("./csv/rewards.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	var rewards []RewardsData
	err = csv.Unmarshal(data, &rewards)
	if err != nil {
		panic(err)
	}

	rewardsMap = make(map[uint32][]RewardsData)
	for _, v := range rewards {
		list, ok := rewardsMap[v.Typeid]
		if !ok {
			list = make([]RewardsData, 0)
		}
		list = append(list, v)
		rewardsMap[v.Typeid] = list
	}
	glog.Infoln("奖励：", len(rewardsMap))
}
