package csv

import (
	"basic/csv"
	"io/ioutil"
	"os"

	"github.com/golang/glog"
)

var roomMap map[uint32]CsvRoom

type CsvRoom struct {
	Rtype        uint32 `csv:"rtype"`        //
	Ante         uint32 `csv:"ante"`         //
	Level        uint32 `csv:"level"`        //
	Kind         uint32 `csv:"kind"`         //
	Time         uint32 `csv:"time"`         // //
	Number       uint32 `csv:"number"`       // //
	Percent      uint32 `csv:"percent"`      // //
	Access       uint32 `csv:"access"`       //
	Accessupper  uint32 `csv:"accessupper "` // //
	Maxcount     uint32 `csv:"maxcount"`     //  //
	Costone      uint32 `csv:"costone"`      // //
	Costonecount uint32 `csv:"costonecount"` // //
	Costtwo      uint32 `csv:"costtwo"`      // //
	Costtwocount uint32 `csv:"costtwocount"` // //
	Seniority    uint32 `csv:"seniority"`    // //
}

var csvroomlist []CsvRoom

func GetRoomList() []CsvRoom {
	return csvroomlist
}
func GetRoom(kind uint32) *CsvRoom {
	data, ok := roomMap[kind]
	if !ok {
		return nil
	}
	return &data
}
func init() {
	f, err := os.Open("./csv/room.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	err = csv.Unmarshal(data, &csvroomlist)
	if err != nil {
		panic(err)
	}
	roomMap = make(map[uint32]CsvRoom)
	for _, v := range csvroomlist {
		roomMap[v.Rtype] = v
	}

	glog.Infoln("房间表:", len(roomMap))
}
