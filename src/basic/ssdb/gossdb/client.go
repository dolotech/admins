package gossdb

import (
	"basic/ssdb/goerr"
	"basic/ssdb/to"
	"basic/utils"
	"encoding/json"
	"strconv"
	"time"
)

//可回收的连接，支持连接池。
//非协程安全，多协程请使用多个连接。
type Client struct {
	Client   *SSDBClient
	lastTime time.Time //最后的更新时间
	isOpen   bool      //是否已连接
	password string    //校验密码
	IP       string
	Port     int
	Pool     chan *Client
}

//打开连接
func (this *Client) Start() error {
	//	if this.Client == nil {
	this.Client = &SSDBClient{IP: this.IP, Port: this.Port}
	//	}
	err := this.Client.Connect()
	return err
}
func (this *Client) Close() error {
	return this.Client.Close()
}

//检查连接情况
//
//  返回 bool，如果可以正常查询数据库信息，就返回true，否则返回false
func (this *Client) Ping() bool {
	_, err := this.Info()
	return err == nil
}

//查询数据库大小
//
//  返回 re，返回数据库的估计大小, 以字节为单位. 如果服务器开启了压缩, 返回压缩后的大小.
//  返回 err，执行的错误
func (this *Client) DbSize() (re int, err error) {
	resp, err := this.Do("dbsize")
	if err != nil {
		return -1, err
	}
	if len(resp) == 2 && resp[0][0] == ok[0] && resp[0][1] == ok[1] {
		return strconv.Atoi(string(resp[1]))
	}
	return -1, makeError(resp)
}

//返回服务器的信息.
//
//  返回 re，返回数据库的估计大小, 以字节为单位. 如果服务器开启了压缩, 返回压缩后的大小.
//  返回 err，执行的错误
func (this *Client) Info() (re []Value, err error) {
	resp, err := this.Do("info")
	if err != nil {
		return nil, err
	}
	if len(resp) > 1 && resp[0][0] == ok[0] && resp[0][1] == ok[1] {
		return resp[1:], nil
	}
	return nil, makeError(resp)
}

//对数据进行编码
func (this *Client) encoding(value interface{}, hasArray ...bool) string {
	var result string
	switch t := value.(type) {
	//	case *int, *int8, *int16, *int32, *int64, *uint, *uint8, *uint16, *uint32, *uint64, *float32, *float64, *complex64, *complex128:
	//		return to.String(*t)
	case int, int8, int16, int32, int64, uint, uint8, uint16, uint32, uint64, float32, float64, complex64, complex128:
		return to.String(t)
	case string: //byte==uint8
		result = t
	case []byte:
		return utils.String(t)
	case bool:
		if t {
			result = "1"
		} else {
			result = "0"
		}
	case nil:
		result = ""
	case []bool, []string, []int, []int8, []int16, []int32, []int64, []uint, []uint16, []uint32, []uint64, []float32, []float64, []interface{}:
		if len(hasArray) > 0 && hasArray[0] {
			if bs, err := json.Marshal(value); err == nil {
				return utils.String(bs)
			}
		}
		result = "can not support slice,please open the Encoding options"
	default:
		if bs, err := json.Marshal(value); err == nil {
			return utils.String(bs)
		}
		result = "not open Encoding options"
	}
	return result
}

//生成通过的错误信息，已经确定是有错误
func makeError(resp []Value, errKey ...interface{}) error {
	if len(resp) < 1 {
		return goerr.New("ssdb respone error")
	}
	//正常返回的不存在不报错，如果要捕捉这个问题请使用exists
	if string(resp[0]) == "not_found" {
		return nil
	}
	if len(errKey) > 0 {
		return goerr.New("access ssdb error, code is %v", errKey)
	} else {
		return goerr.New("access ssdb error, code is ")
	}
}

//通用调用方法，如果有需要在所有方法前执行的，可以在这里执行
func (this *Client) Do(args ...interface{}) ([]Value, error) {
	resp, err := this.Client.Do(args...)
	if err != nil {
		for {
		this.Close()
		err = this.Start()
		if err ==nil{
					break
		}else{
			<- time.After(time. Millisecond*20)
		}
	}
		if err == nil {
			resp, err = this.Client.Do(args...)
		} 
	} 	
	if this.Pool != nil {
		this.Pool <- this
	}
	return resp, err
}
