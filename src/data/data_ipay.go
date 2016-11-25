package data

import (
	"basic/ssdb/gossdb"
	"encoding/json"
)

// 交易结果通知
type TradingResults struct {
	Transtype int     `json:"transtype"` // 交易类型0–支付交易；
	Cporderid string  `json:"cporderid"` // 商户订单号
	Transid   string  `json:"transid"`   // 交易流水号
	Appuserid string  `json:"appuserid"` // 用户在商户应
	Appid     string  `json:"appid"`     // 游戏id
	Waresid   uint32  `json:"waresid"`   // 商品编码
	Feetype   int     `json:"feetype"`   // 计费方式
	Money     float32 `json:"money"`     // 交易金额
	Currency  string  `json:"currency"`  // 货币类型	RMB
	Result    int     `json:"result"`    // 交易结果	0–交易成功 1–交易失败
	Transtime string  `json:"transtime"` // 交易完成时间 yyyy-mm-dd hh24:mi:ss
	Cpprivate string  `json:"cpprivate"` // 商户私有信息
	Paytype   uint32  `json:"paytype"`   // 支付方式
}

// 下单请求参数
type IapppayOrder struct {
	Appid         string `json:"appid"`         // 应用编号
	Waresid       uint32 `json:"waresid"`       // 商品编号
	Waresname     string `json:"waresname"`     // 商品名称
	Cporderid     string `json:"cporderid"`     // 商户订单号
	Price         uint32 `json:"price"`         // 支付金额
	Currency      string `json:"currency"`      // 货币类型
	Appuserid     string `json:"appuserid"`     // 用户在商户应用的唯一标识
	Cpprivateinfo string `json:"cpprivateinfo"` // 商户私有信息
	Notifyurl     string `json:"notifyurl"`     // 支付结果通知地址
}

// 下单请求失败结果
type TransData struct {
	Code   json.Number `json:"code,Number"`
	Errmsg string      `json:"errmsg"`
}

// 下单请求成功结果
type TransDataId struct {
	Transid string `json:"transid"`
}

// 主动请求交易结果请求参数
type IapppayQuery struct {
	Appid     string `json:"appid"`     // 应用编号
	Cporderid string `json:"cporderid"` // 商户订单号
}

// 主动请求交易结果
type QueryResults struct {
	Cporderid string  `json:"cporderid"` // 商户订单号
	Transid   string  `json:"transid"`   // 交易流水号
	Appuserid string  `json:"appuserid"` // 用户在商户应
	Appid     string  `json:"appid"`     // 游戏id
	Waresid   uint32  `json:"waresid"`   // 商品编码
	Feetype   int     `json:"feetype"`   // 计费方式
	Money     float32 `json:"money"`     // 交易金额
	Currency  string  `json:"currency"`  // 货币类型	RMB
	Result    int     `json:"result"`    // 交易结果	0–交易成功 1–交易失败
	Transtime string  `json:"transtime"` // 交易完成时间 yyyy-mm-dd hh24:mi:ss
	Cpprivate string  `json:"cpprivate"` // 商户私有信息
	Paytype   uint32  `json:"paytype"`   // 支付方式
}

// 交易记录
type ChargeOrder struct {
	Orderid   string // 订单号
	Userid    string // userid
	Phone     string // phone
	Transid   string // 流水号
	Waresid   uint32 // 商品编号
	Money     string // 交易金额
	Platform  uint32 // 平台
	OrderRes  uint32 // 下单结果 0成功,1失败
	Ctime     int64  // 创建时间(下单时间)
	Result    uint32 // 交易结果 0成功,1失败
	Transtime string // 交易完成时间
	Status    uint32 // 发货结果 0成功,1失败
}

func GetTransition(day string, page int, limit int) ([]*TradingResults, int64, error) {
	size, err := gossdb.C().Qsize(KEY_TRADINGRESULTS + day)
	if err != nil {
		return nil, 0, err
	}
	offset := page*limit - int(size)
	value, err := gossdb.C().Qrange(KEY_TRADINGRESULTS+day, offset, limit)
	if err != nil {
		return nil, 0, err
	}
	list := make([]*TradingResults, 0, len(value))
	for _, v := range value {
		data := &TradingResults{}
		err := v.As(data)
		if err == nil {
			list = append(list, data)
		}
	}
	return list, size, nil
}
func GetTransitionByUserid(day string, userid string, page int, limit int) ([]*TradingResults, int64, error) {
	size, err := gossdb.C().Qsize(KEY_TRADINGRESULTS + day + userid)
	if err != nil {
		return nil, 0, err
	}
	offset := page*limit - int(size)
	value, err := gossdb.C().Qrange(KEY_TRADINGRESULTS+day+userid, offset, limit)
	if err != nil {
		return nil, 0, err
	}
	list := make([]*TradingResults, 0, len(value))
	for _, v := range value {
		data := &TradingResults{}
		err := v.As(data)
		if err == nil {
			list = append(list, data)
		}
	}
	return list, size, nil
}
func GetChargeOrder(day string, page int, limit int) ([]*ChargeOrder, int64, error) {
	size, err := gossdb.C().Qsize(KEY_USER_CHARGEORDER + day)
	if err != nil {
		return nil, 0, err
	}
	offset := page*limit - int(size)
	value, err := gossdb.C().Qrange(KEY_USER_CHARGEORDER+day, offset, limit)
	if err != nil {
		return nil, 0, err
	}
	list := make([]*ChargeOrder, 0, len(value))
	for _, v := range value {
		data := &ChargeOrder{}
		err := v.As(data)
		if err == nil {
			list = append(list, data)
		}
	}
	return list, size, nil

}
func GetChargeOrderByUserid(day string, userid string, page int, limit int) ([]*ChargeOrder, int64, error) {
	size, err := gossdb.C().Qsize(KEY_USER_CHARGEORDER + day + userid)
	if err != nil {
		return nil, 0, err
	}
	offset := page*limit - int(size)
	value, err := gossdb.C().Qrange(KEY_USER_CHARGEORDER+day+userid, offset, limit)
	if err != nil {
		return nil, 0, err
	}
	list := make([]*ChargeOrder, 0, len(value))
	for _, v := range value {
		data := &ChargeOrder{}
		err := v.As(data)
		if err == nil {
			list = append(list, data)
		}
	}
	return list, size, nil

}
