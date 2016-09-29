/**********************************************************
 * Author : Michael
 * Email : dolotech@163.com
 * Last modified : 2016-07-07 23:40
 * Filename : iplocation.go
 * Description : 简介: 根据用户的IP地址，在纯真IP数据库中检索用户的实体地址
 * *******************************************************/
import (
	"encoding/binary"
	"io/ioutil"
	"net"
	"os"
	"strings"
	"sync"

	log "github.com/golang/glog"
	zh "golang.org/x/text/encoding/simplifiedchinese"
	"golang.org/x/text/transform"
)

const QqwryFile = "./qqwry.dat"

type Ip2LocationResp struct {
	Ok      bool
	IP      string
	Country string
	Area    string
}

var queryMutex sync.RWMutex

func Query(ipaddress string) *Ip2LocationResp {
	defer queryMutex.Unlock()
	queryMutex.Lock()
	file, err := os.Open(QqwryFile)

	if err != nil {
		log.Fatal(err)
	}
	buf := make([]byte, 32)
	// header
	file.ReadAt(buf[0:8], 0)
	indexStart := int64(binary.LittleEndian.Uint32(buf[0:4]))
	indexEnd := int64(binary.LittleEndian.Uint32(buf[4:8]))
	log.Infoln("Index range: %d - %d", indexStart, indexEnd)
	ip := net.ParseIP(ipaddress)
	ip4 := make([]byte, 4)
	ip4 = ip.To4() // := &net.IPAddr{IP:ip}
	//二分法查找
	maxLoop := int64(32)
	head := indexStart //+ 8
	tail := indexEnd   //+ 8
	//是否找到
	got := false
	rpos := int64(0)
	for ; maxLoop >= 0 && len(ip4) == 4; maxLoop-- {
		idxNum := (tail - head) / 7
		pos := head + int64(idxNum/2)*7
		file.ReadAt(buf[0:7], pos)
		// startIP
		_ip := binary.LittleEndian.Uint32(buf[0:4])
		//记录位置
		_buf := append(buf[4:7], 0x0) // 3byte + 1byte(0x00)
		rpos = int64(binary.LittleEndian.Uint32(_buf))
		file.ReadAt(buf[0:4], rpos)
		_ip2 := binary.LittleEndian.Uint32(buf[0:4])
		//查询的ip被转成大端了
		_ipq := binary.BigEndian.Uint32(ip4)
		if _ipq > _ip2 {
			head = pos
			continue
		}
		if _ipq < _ip {
			tail = pos
			continue
		}
		got = true
	}
	loc := &Ip2LocationResp{
		Ok:      false,
		IP:      ipaddress,
		Country: "",
		Area:    "",
	}
	if got {
		_loc := getIpLocation(file, rpos)
		var tr *transform.Reader
		tr = transform.NewReader(strings.NewReader(_loc.Country), zh.GBK.NewDecoder())

		if s, err := ioutil.ReadAll(tr); err == nil {
			loc.Country = string(s)
		}

		tr = transform.NewReader(strings.NewReader(_loc.Area), zh.GBK.NewDecoder())

		if s, err := ioutil.ReadAll(tr); err == nil {
			loc.Area = string(s)
		}
		loc.Ok = _loc.Ok

	}
	return loc
}

func getIpLocation(file *os.File, offset int64) (loc Ip2LocationResp) {
	buf := make([]byte, 1024)
	file.ReadAt(buf[0:1], offset+4)
	mod := buf[0]

	countryOffset := int64(0)
	areaOffset := int64(0)

	if 0x01 == mod {
		countryOffset = _readLong3(file, offset+5)

		file.ReadAt(buf[0:1], countryOffset)

		mod2 := buf[0]

		if 0x02 == mod2 {
			loc.Country = _readString(file, _readLong3(file, countryOffset+1))
			areaOffset = countryOffset + 4
		} else {
			loc.Country = _readString(file, countryOffset)
			areaOffset = countryOffset + int64(len(loc.Country)) + 1
		}

		loc.Area = _readArea(file, areaOffset)

	} else if 0x02 == mod {
		loc.Country = _readString(file, _readLong3(file, offset+5))
		loc.Area = _readArea(file, offset+8)
	} else {
		loc.Country = _readString(file, offset+4)
		areaOffset = offset + 4 + int64(len(loc.Country)) + 1

		loc.Area = _readArea(file, areaOffset)
	}

	loc.Ok = true
	return loc
}

func _readArea(file *os.File, offset int64) string {
	buf := make([]byte, 4)
	file.ReadAt(buf[0:1], offset)
	mod := buf[0]

	if 0x01 == mod || 0x02 == mod {
		areaOffset := _readLong3(file, offset+1)
		if areaOffset == 0 {
			return ""
		} else {
			return _readString(file, areaOffset)
		}
	}
	return _readString(file, offset)
}

func _readLong3(file *os.File, offset int64) int64 {
	buf := make([]byte, 4)
	file.ReadAt(buf, offset)
	buf[3] = 0x00

	return int64(binary.LittleEndian.Uint32(buf))
}

func _readString(file *os.File, offset int64) string {
	buf := make([]byte, 1024)
	got := int64(0)
	for ; got < 1024; got++ {
		file.ReadAt(buf[got:got+1], offset+got)

		if buf[got] == 0x00 {
			break
		}
	}

	return string(buf[0:got])
}
