package csv

import (
	"basic/csv"
	"io/ioutil"
	"os"
)

var explist map[uint32]ExpData

type ExpData struct {
	Id  uint32 `csv:"id"`  //
	Exp uint32 `csv:"exp"` //
}

func GetExp(id uint32) *ExpData {
	exp, ok := explist[id]
	if ok {
		return &exp
	} else {
		return nil
	}
}
func init() {
	f, err := os.Open("./csv/exp.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	var exp []ExpData
	err = csv.Unmarshal(data, &exp)
	if err != nil {
		panic(err)
	}
	explist = make(map[uint32]ExpData)
	for _, v := range exp {
		explist[v.Id] = v
	}
}
