package data

import (
	"encoding/json"
	"io/ioutil"
	"os"
)


var Version string
var Conf Config

type Config struct {
	Db              SSDB   `json:"db"`
	Log             Scribe `json:"log"`
	Port            string `json:"port"`
	ImageDir        string `json:"imagedir"`
	ImagePort       int    `json:"imageport"`
	Pprof           int    `json:"pprof"`
	Ipay            int    `json:"ipay"`
	ServerId        uint64 `json:"serverid"`
	RobotPort       int    `json:"robotport"`
	RobotIP         string `json:"robotip"`
	Version         string `json:"version"`
	DiscardsTimeout int    `json:"discards_timeout"`
	Mode            int    `json:"mode"`
	CallServer      string `json:"callserver"` // 逻辑服务器提供给管理系统调用的URL
}
type SSDB struct {
	Ip     string `json:"ip"`
	Port   int    `json:"port"`
	Thread int    `json:"thread"`
}
type Scribe struct {
	Ip   string `json:"ip"`
	Port int    `json:"port"`
}

func LoadConf(path string) {
	f, err := os.Open(path)
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	err = json.Unmarshal(data, &Conf)
	if err != nil {
		panic(err)
	}
}
