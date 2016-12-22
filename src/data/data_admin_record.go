package data

import (
	"basic/ssdb/gossdb"
	"time"
)

const (
	OPERATE_KIND_DEL_ADMIN    uint32 = 1 //  删除管理员
	OPERATE_KIND_ADD_ADMIN    uint32 = 2 // 添加管理员
	OPERATE_KIND_MODIFY_ADMIN uint32 = 3 // 修改管理员信息

	OPERATE_KIND_WIDGET_MODIFY uint32 = 4 // 修改玩家道具数量,数量可以是负值
	OPERATE_KIND_PLAYER_MODIFY uint32 = 5 // 修改玩家信息

	OPERATE_KIND_DEL_GROUP    uint32 = 6 //  删除组
	OPERATE_KIND_ADD_GROUP    uint32 = 7 // 添加组
	OPERATE_KIND_MODIFY_GROUP uint32 = 8 // 修改组
	OPERATE_KIND_SEND_MAIL    uint32 = 9 // 发送邮件

)

//管理员操作记录
type AdminRecord struct {
	Timestamp int64  //  系统时间戳
	AdminID   string // 当前操作动作管理员ID
	Kind      uint32 // 操作类型
	WindgetID uint32 // 道具类型
	Count     int    // 操作资源数量,可为负值
	Pre       string // 修改前的值
	After     string // 修改后的值
	Target    string // 目标管理员ID或者玩家ID
	Desc      string
	Param     H // 可以任意附带参数存储
}

//
func (this *AdminRecord) Save() error {
	this.Timestamp = time.Now().Unix()
	_, err := gossdb.C().Qpush_front(ADMIN_RECORD+this.AdminID, this)
	if err != nil {
		return err
	}
	return nil
}
func GetAdminRecord(adminID string, start int, limit int) ([]*AdminRecord, int64, error) {
	size, err := gossdb.C().Qsize(ADMIN_RECORD + adminID)
	if err != nil {
		return nil, 0, err
	}
	value, err := gossdb.C().Qrange(ADMIN_RECORD+adminID, start, limit)
	if err != nil {
		return nil, 0, err
	}
	m := make([]*AdminRecord, 0, len(value))
	for _, v := range value {
		d := &AdminRecord{}
		if err := v.As(d); err == nil {
			m = append(m, d)
		}
	}
	return m, size, nil
}
