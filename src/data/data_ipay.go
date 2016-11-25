package data

import (
	"basic/ssdb/gossdb"
	"basic/utils"
	"encoding/json"
	"strconv"
	"time"

	"github.com/golang/glog"
)

var NotifyClUrl string = "/mahjong/iapppay/notice"

//爱贝商户后台接入url
const iapppayCpUrl = "http://ipay.iapppay.com:9999"

//下单接口 url
const ORDERURL = iapppayCpUrl + "/payapi/order"

//支付结果查询接口 url
const queryResultUrl = iapppayCpUrl + "/payapi/queryresult"

//应用编号
const APPID = "3006208675"

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

// 检测是否发货
func CheckDelivery(transid, cporderid string) bool {
	d := &ChargeOrder{Orderid: cporderid, Transid: transid}
	err := d.Get()
	if err == nil && d.Result == 0 && d.Transtime != "" {
		return true // 已经发货
	}
	return false // 没有发货
}
func ChargeOrderLog(Phone string, Platform uint32, Status uint32, t *TradingResults) {
	m := map[string]interface{}{
		"Phone":     Phone,       // Phone
		"Platform":  Platform,    // 平台
		"Result":    t.Result,    // 交易结果 0成功,1失败
		"Transtime": t.Transtime, // 交易完成时间
		"Status":    Status,      // 发货结果 0成功,1失败
	}
	MsetChargeOrder(t.Cporderid, m)
}

//用时间截+用户id做订单号
func GenCporderid(userid string) string {
	t := time.Now().UnixNano()
	id := userid + strconv.Itoa(int(t))
	SaveCporderid(id, t)
	return id
}

//存储订单号
func SaveCporderid(id string, time int64) error {
	today := utils.TimestampToday()
	str := strconv.Itoa(int(today))
	err := gossdb.C().Hset(KEY_CPORDERID+str, id, time)
	if err != nil {
		glog.Errorln("SaveCporderid error:", err)
	}
	return err
}

// 交易结果设置
func SetChargeOrder(Transid, key string, val interface{}) error {
	err := gossdb.C().Hset(KEY_CHARGEORDER+Transid, key, val)
	if err != nil {
		glog.Errorln("SetChargeOrder error:", err)
	}
	return err
}

func MsetChargeOrder(Transid string, kvs map[string]interface{}) error {
	err := gossdb.C().MultiHset(KEY_CHARGEORDER+Transid, kvs)
	if err != nil {
		glog.Errorln("MsetChargeOrder error:", err)
	}
	return err
}

// 交易记录
func (this *ChargeOrder) Exist() (bool, error) {
	return gossdb.C().Hexists(KEY_CHARGEORDER+this.Orderid, this.Orderid)
}

func (this *ChargeOrder) Get() error {
	return gossdb.C().GetObject(KEY_CHARGEORDER+this.Orderid, this)
}

func (this *ChargeOrder) Save() error {
	err := gossdb.C().PutObject(KEY_CHARGEORDER+this.Orderid, this)
	gossdb.C().Qpush_front(KEY_USER_CHARGEORDER+this.Userid, this)
	return err

}

// 交易结果通知记录
func (this *TradingResults) Get() error {
	return gossdb.C().GetObject(KEY_TRADINGRESULTS+this.Cporderid, this)
}

func (this *TradingResults) Save() error {
	gossdb.C().Qpush_front(KEY_TRADINGRESULTS+this.Appuserid, this)
	return gossdb.C().PutObject(KEY_TRADINGRESULTS+this.Cporderid, this)
}

// 获取某玩家的所有离线订单，用于上线补单
func GetTradingOff(userid string) ([]*TradingResults, error) {
	hash, err := gossdb.C().HgetAll(KEY_TRADINGOFFLINE + userid)
	if err != nil {
		return nil, err
	}
	list := make([]*TradingResults, 0, len(hash))
	for _, v := range hash {
		data := &TradingResults{}
		v.As(data)
		list = append(list, data)
	}
	return list, nil
}

// 保存离线订单,用于下次上线补单
func (this *TradingResults) SaveTradingOff() error {
	return gossdb.C().Hset(KEY_TRADINGOFFLINE+this.Appuserid, this.Cporderid, this)
}

// 补单完成，删除订单
func DelTradingOff(userid string) error {
	return gossdb.C().Hclear(KEY_TRADINGOFFLINE + userid)
}
