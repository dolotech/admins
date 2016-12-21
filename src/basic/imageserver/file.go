/**********************************************************
 * Author : Michael
 * Email : dolotech@163.com
 * Last modified : 2016-06-11 16:27
 * Filename : file.go
 * Description :
 * *******************************************************/
package imageserver

import (
	"basic/utils"
	"fmt"
	"os"

	"github.com/golang/glog"
)

func imageID2Path(path string, imageid string) string {
	return fmt.Sprintf("%s/%s/%s/%s/%s.jpg", path, imageid[0:3], imageid[3:6], imageid[6:9], imageid[9:])
}

func makeImageID() string {
	str := utils.Base62encode(uint64(utils.RandInt64()))
	if len(str) < 11 {
		var buf = make([]byte, 11-len(str))
		for i := 0; i < 11-len(str); i++ {
			buf[i] = 48
		}
		str = string(buf) + str
	}
	glog.Infoln(str[:11])
	return str[:11]
}

func fileExist(filename string) bool {
	if _, err := os.Stat(filename); err != nil {
		return false
	} else {
		return true
	}
}

func buildTree(path string, imageid string) error {
	return os.MkdirAll(fmt.Sprintf("%s/%s/%s/%s", path, imageid[0:3], imageid[3:6], imageid[6:9]), 0777)
}
