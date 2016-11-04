package csv

import (
	"basic/csv"
	"io/ioutil"
	"os"

	"github.com/golang/glog"
)

var activityMap map[uint32]ActivityData

type ActivityData struct {
	Id        uint32 `csv:"id"`
	Type      uint32 `csv:"sequence"`
	Count     uint32 `csv:"stage"`
	Rewards   uint32 `csv:"rewards"`
	Starttime uint32 `csv:"starttime"`
	Endtime   uint32 `csv:"endtime"`
}

var activity []ActivityData

func GetActivityData() []ActivityData {
	return activity
}

func GetActivity(id uint32) *ActivityData {
	activity := activityMap[id]
	return &activity
}

func init() {
	f, err := os.Open("./csv/activity.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	err = csv.Unmarshal(data, &activity)
	if err != nil {
		panic(err)
	}
	activityMap = make(map[uint32]ActivityData)
	for _, v := range activity {
		activityMap[v.Id] = v
	}
	glog.Infoln("活动表：", len(activityMap))
}
