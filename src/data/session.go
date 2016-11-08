package data

import (
	"basic/ssdb/gossdb"
	"basic/utils"
	"strconv"
	"time"
)

//var Session map[string]string

//func init() {
//	Session = make(map[string]string)
//}

type Session struct {
	Username string
	Password string
	Expire   uint32
}

func (this *Session) Save() (string, error) {
	this.Expire = uint32(time.Now().Unix() + 86400*30)
	key := utils.Md5(this.Username + ":" + this.Password + ":" + strconv.Itoa(int(this.Expire)))
	err := gossdb.C().Hset(LOGIN_SESSION, key, this)
	return key, err
}
func (this *Session) Del(key string) error {
	err := gossdb.C().Hdel(LOGIN_SESSION, key)
	return err
}
func (this *Session) Get(key string) error {
	value, err := gossdb.C().Hget(LOGIN_SESSION, key)
	value.As(this)
	return err
}
