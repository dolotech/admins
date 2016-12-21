/**********************************************************
 * Author : Michael
 * Email : dolotech@163.com
 * Last modified : 2016-06-11 16:22
 * Filename : widget.go
 * Description :  游戏道具的数据
 * *******************************************************/
package data

type WidgetData struct {
	Id    uint32
	Count uint32
}

func (this *WidgetData) GetId() uint32 {
	return this.Id
}
func (this *WidgetData) GetCount() uint32 {
	return this.Count
}

func (this *WidgetData) SetId(value uint32) {
	this.Id = value
}
func (this *WidgetData) SetCount(value uint32) {
	this.Count = value
}

func (this *WidgetData) GetData() *WidgetData {
	return this
}
