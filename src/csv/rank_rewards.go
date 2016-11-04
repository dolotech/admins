/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-05-03 20:37
 * Filename      : tb_exp.go
 * Description   :
 * *******************************************************/
package csv

import (
	"basic/csv"
	"io/ioutil"
	"os"
	"strconv"

	"github.com/golang/glog"
)

var rankRewardsMap map[string]CsvRankRewards

type CsvRankRewards struct {
	Id      uint32 `csv:"id"`      // // //
	Kind    uint32 `csv:"kind"`    // ////
	Rank    uint32 `csv:"rank"`    // ////
	Rewards uint32 `csv:"rewards"` // ////
}

func GetRankRewards(kind uint32, rank uint32) *CsvRankRewards {
	data, ok := rankRewardsMap[strconv.Itoa(int(kind))+strconv.Itoa(int(rank))]
	if ok {
		return &data
	}
	return nil
}
func init() {
	f, err := os.Open("./csv/rank_rewards.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	var list []CsvRankRewards
	err = csv.Unmarshal(data, &list)
	if err != nil {
		panic(err)
	}
	rankRewardsMap = make(map[string]CsvRankRewards)
	for _, v := range list {
		rankRewardsMap[strconv.Itoa(int(v.Kind))+strconv.Itoa(int(v.Rank))] = v
	}
	glog.Infoln("排行榜奖励表：", len(rankRewardsMap))
}
