package csv

import (
	"algo"
	"basic/csv"
	"io/ioutil"
	"os"
)

var expoutputMap map[uint32]ExpOutputData

type ExpOutputData struct {
	Id     uint32 `csv:"id"`
	AddExp uint32 `csv:"addexp"`
}

func OutputExp(huType uint32) uint32 {
	var exp uint32
	var key uint32
	if huType&algo.HU_ONE_SUIT_LONG_SEVEN_PAIR > 0 { // 清龙对
		key = 8
	} else if huType&algo.HU_LONG_SEVEN_PAIR > 0 { // 龙七对
		key = 7
	} else if huType&algo.HU_ONE_SUIT_SEVEN_PAIR > 0 { // 清七对
		key = 6
	} else if huType&algo.HU_ONE_SUIT_BIG_PAIR > 0 { // 清大对
		key = 5
	} else if huType&algo.HU_ALL_OF_ONE > 0 { // 清一色
		key = 4
	} else if huType&algo.HU_SEVEN_PAIR > 0 { // 七小对
		key = 3
	} else if huType&algo.HU_BIG_PAIR > 0 { // 大对子
		key = 2
	} else if huType&algo.PING_HU > 0 { // 平湖
		key = 1
	}

	if data, ok := expoutputMap[key]; ok {
		exp = data.AddExp
	}
	return exp
}
func init() {
	f, err := os.Open("./csv/exp_output.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	var rewards []ExpOutputData
	err = csv.Unmarshal(data, &rewards)
	if err != nil {
		panic(err)
	}

	expoutputMap = make(map[uint32]ExpOutputData)
	for _, v := range rewards {
		expoutputMap[v.Id] = v
	}
}
