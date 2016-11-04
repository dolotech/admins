package csv

import (
	"basic/csv"
	"io/ioutil"
	"os"
)

var conMap map[uint32]ConData

type ConData struct {
	Id         uint32 `csv:"id"`
	Notstopday uint32 `csv:"notstopday"`
	Rewards    uint32 `csv:"rewards"`
}

func GetCon(id uint32) *ConData {
	data := conMap[id]
	return &data
}
func init() {
	f, err := os.Open("./csv/con_signin_reward.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	var con []ConData
	err = csv.Unmarshal(data, &con)
	if err != nil {
		panic(err)
	}
	conMap = make(map[uint32]ConData)
	for _, v := range con {
		conMap[v.Id] = v
	}
}
