package csv

import (
	"basic/csv"
	"io/ioutil"
	"os"

	"github.com/golang/glog"
)

var bankruptMap map[uint32]CsvBankrupt

type CsvBankrupt struct {
	Id        uint32 `csv:"id"`
	Coin      uint32 `csv:"coin"`
	Condition uint32 `csv:"condition"`
}

var bankruptList []CsvBankrupt

func GetBankruptListLen() int {
	return len(bankruptList)
}

func GetBankrupt(id uint32) *CsvBankrupt {
	data := bankruptMap[id]
	return &data
}
func init() {
	f, err := os.Open("./csv/bankrupt.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	err = csv.Unmarshal(data, &bankruptList)
	if err != nil {
		panic(err)
	}

	bankruptMap = make(map[uint32]CsvBankrupt)
	for _, v := range bankruptList {
		bankruptMap[v.Id] = v
	}
	glog.Infoln("破产表：", len(bankruptList), len(bankruptMap))
}
