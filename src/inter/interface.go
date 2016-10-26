/**********************************************************
 * Author : Michael
 * Email : dolotech@163.com
 * Last modified : 2016-06-11 16:27
 * Filename : interface.go
 * Description :  零散的接口
 * *******************************************************/
package inter

type IHu interface {
	GetHuSeat() uint32
	GetPaoSeat() uint32
	GetCard() byte
	GetClassify() uint32
	SetHuSeat(value uint32)
	SetPaoSeat(value uint32)
	SetCard(value byte)
	SetClassify(value uint32)
	GetZimo() bool
	SetZimo(bool)
}

type IProto interface {
	GetCode() uint32
	Reset()
	String() string
	ProtoMessage()
}
type IPeng interface {
	GetPosition() uint32
	GetCard() byte
}
type IKong interface {
	GetPosition() uint32
	GetCard() byte
	GetClassify() uint32
}
