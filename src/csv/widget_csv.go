/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-05-03 20:37
 * Filename      : tb_exp.go
 * Description   :
 * *******************************************************/
package csv

import (
	"basic/csv"
	"io/ioutil"
	"os"

	"github.com/golang/glog"
)

var widgetMap map[uint32]CsvWidget

type CsvWidget struct {
	Widgetid uint32 `csv:"widgetid"` // //
	Kind     uint32 `csv:"kind"`     // ////
}

func ExistWidget(id uint32) bool {
	_, ok := widgetMap[id]
	return ok
}
func init() {
	f, err := os.Open("./csv/widget.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	var list []CsvWidget
	err = csv.Unmarshal(data, &list)
	if err != nil {
		panic(err)
	}
	widgetMap = make(map[uint32]CsvWidget)
	for _, v := range list {
		widgetMap[v.Widgetid] = v
	}
	glog.Infoln("道具表：", len(widgetMap))
}
