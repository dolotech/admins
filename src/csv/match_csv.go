/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-06-09 16:17
 * Filename      : tb_match_room_config.go
 * Description   :
 * *******************************************************/
package csv

import (
	"basic/csv"
	"io/ioutil"
	"os"

	"github.com/golang/glog"
)

var matchMap map[uint32]CsvMatch

type CsvMatch struct {
	Kind            uint32 `csv:"kind"`
	Level           uint32 `csv:"level"`
	Playercount     uint32 `csv:"playercount"` // 每场人数
	Consume         uint32 `csv:"consume"`     // 入场券消耗
	Roomtype        uint32 `csv:"roomtype"`    // 房间类型id
	Cost            uint32 `csv:"cost"`        // 金币消耗
	Plan            uint32 `csv:"plan"`        // 1每天2每周3每月
	Starttime       string `csv:"starttime"`
	Endtime         string `csv:"endtime"`
	Weekystart      uint32 `csv:"weekystart"`
	Weekyend        uint32 `csv:"weekyend"`
	Dailystart      uint32 `csv:"dailystart"`
	Dailyend        uint32 `csv:"dailyend"`
	Championnumbers uint32 `csv:"championnumbers"`
	ChampionAward   uint32 `csv:"championAward"`
	Runnernumber    uint32 `csv:"runnernumber"`
	RunnerAward     uint32 `csv:"runnerAward"`
	Thirdnumber     uint32 `csv:"thirdnumber"`
	Thirdaward      uint32 `csv:"thirdaward"`
	Gametimes       uint32 `csv:"gametimes"`  // 总局数: 0表示无限制
	Matchtimes      uint32 `csv:"matchtimes"` // 总场次: 0表示无限制
	Dieout          uint32 `csv:"dieout"`     // 每轮淘汰人数
	Status          uint32 `csv:"status"`     // 开启状态
}

func GetMatchMap() map[uint32]CsvMatch {
	return matchMap
}

func GetMatchRoom(id uint32) *CsvMatch {
	data := matchMap[id]
	return &data
}

func init() {
	f, err := os.Open("./csv/match.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	var list []CsvMatch
	err = csv.Unmarshal(data, &list)
	if err != nil {
		panic(err)
	}

	matchMap = make(map[uint32]CsvMatch)
	for _, v := range list {
		matchMap[v.Kind] = v
	}
	glog.Infoln("比赛房间表：", len(matchMap))
}
