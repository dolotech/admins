package data

import (
	"basic/utils"
	"strconv"
	"sync"
	"time"
)

var Sessions *SessionList

type SessionList struct {
	hash map[string]*Session
	sync.RWMutex
}

func (this *SessionList) Del(key string) {
	this.Lock()
	defer this.Unlock()
	delete(this.hash, key)
}
func (this *SessionList) Get(key string) *Session {
	this.RLock()
	defer this.RUnlock()
	if ses, ok := this.hash[key]; ok {
		if ses.Expire < uint32(time.Now().Unix()) {
			return nil
		}
		return ses
	}
	return nil
}
func (this *SessionList) Add(ses *Session) string {
	this.Lock()
	defer this.Unlock()
	ses.Expire = uint32(time.Now().Unix() + 86400)
	key := utils.Md5(ses.Username + ":" + ses.Password + ":" + strconv.Itoa(int(ses.Expire)))
	this.hash[key] = ses
	return key
}

func init() {
	Sessions = &SessionList{hash: make(map[string]*Session)}
}

type Session struct {
	Username string
	Password string
	Expire   uint32
}

//func (this *Session) Save() (string, error) {
//	this.Expire = uint32(time.Now().Unix() + 86400)
//	key := utils.Md5(this.Username + ":" + this.Password + ":" + strconv.Itoa(int(this.Expire)))
//	//	err := gossdb.C().Hset(LOGIN_SESSION, key, this)
//	//sessions.Add(key, this)
//	return key, err
//}
//func (this *Session) Del(key string) error {
//	err := gossdb.C().Hdel(LOGIN_SESSION, key)
//	return err
//}

//func (this *Session) Get(key string) error {
//	value, err := gossdb.C().Hget(LOGIN_SESSION, key)
//	if err == nil {
//		return value.As(this)
//	}
//	return err
//}
