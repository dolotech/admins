package data

import (
	"basic/ssdb/gossdb"
	"time"
)

type LoginLimitIP struct {
	IP            string
	Userids       []string
	LastLoginTime int64
	Count         uint32
}

var loginErrIPHash map[string]*LoginLimitIP

func GetLimitIPHash() []*LoginLimitIP {
	list := make([]*LoginLimitIP, 0, len(loginErrIPHash))
	for _, v := range loginErrIPHash {
		list = append(list, v)
	}
	return list
}
func DelLimitIPInDb(ip string) error {
	delete(loginErrIPHash, ip)
	return gossdb.C().Hdel(ADMIN_LOGIN_IP_LIMIT, ip)
}
func LoadLimitIPs() {
	loginErrIPHash = make(map[string]*LoginLimitIP)
	m, err := gossdb.C().MultiHgetAll(ADMIN_LOGIN_IP_LIMIT)
	if err == nil {
		for k, v := range m {
			d := &LoginLimitIP{}
			if err := v.As(d); err == nil {
				loginErrIPHash[k] = d
			}
		}
	}
}

func GetLimitCount(ip string) *LoginLimitIP {
	if d, ok := loginErrIPHash[ip]; ok {
		return d
	}
	return nil
}
func AddLimitCount(ip string, userid string) {
	if d, ok := loginErrIPHash[ip]; !ok {
		d = &LoginLimitIP{IP: ip}
		//	d.Userids = append(d.Userids, userid)
		loginErrIPHash[ip] = d

	}
	loginErrIPHash[ip].Count++
	loginErrIPHash[ip].LastLoginTime = time.Now().Unix()
	loginErrIPHash[ip].Userids = append(loginErrIPHash[ip].Userids, userid)
	if loginErrIPHash[ip].Count >= 5 {
		gossdb.C().Hset(ADMIN_LOGIN_IP_LIMIT, ip, loginErrIPHash[ip])
	}
}
func DelLimitLogin(ip string) {
	delete(loginErrIPHash, ip)
}
