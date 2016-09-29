/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-01-23 10:24
 * Filename      : packet.go
 * Description   : 字符数组类型到其他go数据类型的转换
 * *******************************************************/

package gossdb

import (
	"basic/utils"
	"encoding/json"
	"errors"
	"fmt"
	"reflect"
	"strconv"
)

//扩展值，原始类型为 string
type Value []byte

//返回 string 的值
func (this Value) String() string {
	var b []byte = this
	return utils.String(b)
}

//返回 int64 的值
func (this Value) Int64() int64 {
	i, _ := strconv.ParseInt(string(this), 10, 64)
	return i
}

//返回 int32 的值
func (this Value) Int32() int32 {
	i, _ := strconv.ParseInt(string(this), 10, 32)
	return int32(i)
}

//返回 int16 的值
func (this Value) Int16() int16 {
	i, _ := strconv.ParseInt(string(this), 10, 16)
	return int16(i)

}

//返回 int8 的值
func (this Value) Int8() int8 {
	i, _ := strconv.ParseInt(string(this), 10, 8)
	return int8(i)
}

//返回 int 的值
func (this Value) Int() int {
	i, _ := strconv.ParseInt(string(this), 10, 64)
	return int(i)
}

//返回 uint64 的值
func (this Value) UInt64() uint64 {
	i, _ := strconv.ParseUint(string(this), 10, 64)
	return i
}

//返回 uint32 类型的值
func (this Value) UInt32() uint32 {
	i, _ := strconv.ParseUint(string(this), 10, 32)
	return uint32(i)
}

//返回 uint16 类型的值
func (this Value) UInt16() uint16 {
	i, _ := strconv.ParseUint(string(this), 10, 16)
	return uint16(i)
}

//返回 uint8 类型的值
func (this Value) UInt8() uint8 {
	i, _ := strconv.ParseUint(string(this), 10, 8)
	return uint8(i)

}

func (this Value) Bytes() []byte {
	return []byte(this)
}

//返回 uint 类型的值
func (this Value) UInt() uint {
	i, _ := strconv.ParseUint(string(this), 10, 64)
	return uint(i)
}

//返回 float64 类型的值
func (this Value) Float64() float64 {
	i, _ := strconv.ParseFloat(string(this), 64)
	return i
}

//返回 float32 类型的值
func (this Value) Float32() float32 {
	i, _ := strconv.ParseFloat(string(this), 32)
	return float32(i)

}

//返回 bool 类型的值
//var boo = []byte("true")

func (this Value) Bool() bool {
	return string(this) == "1"
	//	b := true
	//	for k, v := range this {
	//		if v != boo[k] {
	//			return false
	//		}
	//	}
	//	return b
}

//返回 time.Time 类型的值
//func (this Value) Time() time.Time {
//	return to.Time(this)
//}
//
////返回 time.Duration 类型的值
//func (this Value) Duration() time.Duration {
//	return to.Duration(this)
//}

//判断是否为空
func (this Value) IsEmpty() bool {
	return len(this) == 0
}

//按json 转换指定类型
//
//  value 传入的指针
//
//示例
//  var abc time.Time
//  v.As(&abc)
func (this Value) As(value interface{}) (err error) {
	pv := reflect.ValueOf(value)
	if pv.Kind() != reflect.Ptr || pv.IsNil() {
		return errors.New("type is nil or not a pointer")
	}
	err = json.Unmarshal(this, value)
	return
}
func (this Value) Conver(t reflect.Kind) (interface{}, error) {
	//	switch reflect.TypeOf(value).Kind() {
	//	case reflect.Slice:
	//		switch t {
	//		case reflect.String:
	//			if reflect.TypeOf(this).Elem().Kind() == reflect.Uint8 {
	//				return string(this.([]byte)), nil
	//			} else {
	//				return this, nil
	//			}
	//		case reflect.Slice:
	//		default:
	//			return nil, fmt.Errorf("Could not convert slice into non-slice.")
	//		}
	//	case reflect.String:
	//		switch t {
	//		case reflect.Slice:
	//			return this.Bytes(), nil
	//		}
	//	}

	switch t {

	case reflect.String:
		return string(this), nil

	case reflect.Uint64:
		return this.UInt64(), nil
	case reflect.Uint32:
		return this.UInt32(), nil

	case reflect.Uint16:
		return this.UInt16(), nil

	case reflect.Uint8:
		return this.UInt8(), nil

	case reflect.Uint:
		return this.UInt(), nil

	case reflect.Int64:
		return this.Int64(), nil

	case reflect.Int32:
		return this.Int32(), nil

	case reflect.Int16:
		return this.Int16(), nil

	case reflect.Int8:
		return this.Int8(), nil

	case reflect.Int:
		return this.Int(), nil

	case reflect.Float64:
		return this.Float64(), nil

	case reflect.Float32:
		return this.Float32(), nil

	case reflect.Bool:
		return this.Bool(), nil
	case reflect.Slice:
		return this.Bytes(), nil
		//case reflect.Interface:
		//	return this, nil
		//
		//	case KindTime:
		//		return Time(value), nil
		//
		//	case KindDuration:
		//		return Duration(value), nil
		//
	}

	return nil, fmt.Errorf("Could not convert %s into %s.", reflect.TypeOf(this).Kind(), t)
}
