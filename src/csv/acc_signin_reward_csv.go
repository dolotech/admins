package csv

import (
	"basic/csv"
	"io/ioutil"
	"os"
)

var accMap map[uint32]AccData

type AccData struct {
	Id      uint32 `csv:"id"`
	Accday  uint32 `csv:"accday"`
	Rewards uint32 `csv:"rewards"`
}

func GetAcc(id uint32) *AccData {
	data := accMap[id]
	return &data
}
func init() {
	f, err := os.Open("./csv/acc_signin_reward.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	var acc []AccData
	err = csv.Unmarshal(data, &acc)
	if err != nil {
		panic(err)
	}
	accMap = make(map[uint32]AccData)
	for _, v := range acc {
		accMap[v.Id] = v
	}
}
