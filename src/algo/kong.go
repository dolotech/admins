/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-01-23 10:29
 * Filename      : kong.go
 * Description   : 杠牌的数据结构
 * *******************************************************/
package algo

type Kong struct {
	position uint32
	card     byte
	classify uint32
}

func (this *Kong) GetClassify() uint32 {
	return this.classify
}

func (this *Kong) GetCard() byte {
	return this.card
}

func (this *Kong) GetPosition() uint32 {
	return this.position
}
