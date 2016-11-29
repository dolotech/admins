package data

import (
	"basic/ssdb/gossdb"
	"basic/utils"
	"strconv"
	"time"

	"github.com/golang/glog"
)

var ADMIN = "yisen@qq.com"
var admin = &Admin{Id: ADMIN, Passwd: utils.Md5("yisen2016"), Name: "超级管理员", Group_id: 1}

var groupList = []Group{{1, "超级管理员", "拥有最高级权限", 1}, {2, "管理员", "拥有管理玩家权限", 2}}

//
func InitAdmin() {
	for _, v := range groupList {
		boolean, _ := gossdb.C().Hexists(USER_GROUP, strconv.FormatInt(v.Id, 10))
		if !boolean {
			v.Save()
		}
	}
	if !admin.Exist() {
		admin.Save()
	}

}

type Group struct {
	Id    int64
	Name  string
	Desc  string
	Power int
}

func (this *Group) Get() error {
	value, err := gossdb.C().Hget(USER_GROUP, strconv.FormatInt(this.Id, 10))
	if err == nil {
		err = value.As(this)
	}
	return err
}

func (this *Group) Save() error {
	size, _ := gossdb.C().Hsize(USER_GROUP)
	size++
	this.Id = size
	err := gossdb.C().Hset(USER_GROUP_INDEX, this.Name, this.Id)
	err = gossdb.C().Hset(USER_GROUP, strconv.FormatInt(this.Id, 10), this)
	return err
}

func ListGroup() []*Group {
	list := make([]*Group, 0)
	value, err := gossdb.C().Hscan(USER_GROUP, "", "", 50)
	if err == nil {
		for _, v := range value {
			data := &Group{}
			if err := v.As(data); err == nil {
				list = append(list, data)
			}
		}
	}
	return list
}
func (this *Group) Del() error {
	err := gossdb.C().Hdel(USER_GROUP, strconv.FormatInt(this.Id, 10))
	return err
}

// 后台管理员帐号base_admin_user

type Admin struct {
	Id          string //登录账号
	Status      uint32 // '激活状态',
	Passwd      string // '密码(md5)',
	Name        string // '真实姓名',
	Description string // '描述',
	Last_visit  uint32 // '最后登录时间',
	Last_ip     uint32 // '最后登录点IP',
	Login_times uint32 // '登录次数',
	Group_id    int64  // '所属用户组ID',
	Ip_limit    string // 限制登录的IP
	Error_ip    string // '出错的ip',
	Error_time  int64  // '出错时间',
	Error_num   uint32 // '出错次数',
	Members     string // '属下成员后台登录名称',
	Platforms   string
	Create_time uint32 // 账户创建时间
}

var GROUPIDS = map[string]string{
	"1": "超级管理员",
	"2": "普通管理员",
}

func (u *Admin) Get() error {
	value, err := gossdb.C().Hget(USERS, u.Id)
	if err != nil {
		return err
	}
	return value.As(u)
}

// 检测管理员是否存在
func (u *Admin) Exist() bool {
	boolean, _ := gossdb.C().Hexists(USERS, u.Id)
	return boolean
}
func (u *Admin) Save() error {
	u.Create_time = uint32(time.Now().Unix())
	return gossdb.C().Hset(USERS, u.Id, u)
}
func (u *Admin) Del() error {
	return gossdb.C().Hdel(USERS, u.Id)
}
func UserList() ([]*Admin, error) {
	list := make([]*Admin, 0)
	value, err := gossdb.C().MultiHgetAll(USERS)
	if err != nil {
		return list, err
	}
	for _, v := range value {
		data := &Admin{}
		if err := v.As(data); err == nil && data.Id != ADMIN {
			list = append(list, data)
		} else {
			glog.Errorln(err)
		}
	}
	return list, nil
}
