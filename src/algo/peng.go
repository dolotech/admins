/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-01-23 10:29
 * Filename      : peng.go
 * *******************************************************/
package algo

func (this *Peng) GetCard() byte {
	return this.card
}

func (this *Peng) GetPosition() uint32 {
	return this.position
}

type Peng struct {
	position uint32
	card     byte
}
