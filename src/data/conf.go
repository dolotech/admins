package data

import (
	"encoding/json"
	"io/ioutil"
	"os"
)

var Conf Config

type Config struct {
	Db              SSDB   `json:"db"`
	Log             Scribe `json:"log"`
	Port            int    `json:"port"`
	ImageDir        string `json:"imagedir"`
	ImagePort       int    `json:"imageport"`
	Pprof           int    `json:"pprof"`
	Ipay            int    `json:"ipay"`
	ServerId        uint64 `json:"serverid"`
	RobotPort       int    `json:"robotport"`
	RobotIP         string `json:"robotip"`
	Version         string `json:"version"`
	DiscardsTimeout int    `json:"discards_timeout"`
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
