package algo

import "inter"

type Operate struct {
	value uint32
	card  byte
	user  inter.IConn
}

func (this *Operate) SetValue(value uint32) {
	this.value = value
}

func (this *Operate) SetCard(card byte) {
	this.card = card
}
func (this *Operate) SetUser(user inter.IConn) {
	this.user = user
}

func (this *Operate) GetValue() uint32 {
	return this.value
}

func (this *Operate) GetCard() byte {
	return this.card
}
func (this *Operate) GetUser() inter.IConn {
	return this.user
}
