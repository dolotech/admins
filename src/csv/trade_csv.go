package csv

import (
	"basic/csv"
	"io/ioutil"
	"os"
)

var tradeMap map[uint32]TradeData

type TradeData struct {
	Id     uint32 `csv:"id"`     // 兑换表ID
	PropId uint32 `csv:"propid"` // 兑换的物品ID
	Number uint32 `csv:"number"` // 个人兑换的数量限制
	Total  uint32 `csv:"total"`  // 单个月兑换物的数量
	Value  uint32 `csv:"value"`  // 兑换券数量
	Class  uint32 `csv:"class"`  // 1:虚拟，2:实物
}

var trade []TradeData

func GetTradeData() []TradeData {
	return trade
}

func GetTrade(id uint32) *TradeData {
	trade, ok := tradeMap[id]
	if ok {
		return &trade
	} else {
		return nil
	}
}

func init() {
	f, err := os.Open("./csv/trade.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	// var trade []TradeData
	err = csv.Unmarshal(data, &trade)
	if err != nil {
		panic(err)
	}
	tradeMap = make(map[uint32]TradeData)
	for _, v := range trade {
		tradeMap[v.Id] = v
	}
}
