package user

import (
	"basic/ssdb/gossdb"
	"data"

	"github.com/gin-gonic/gin"
)

type Group struct {
	Id    string
	Name  string
	Desc  string
	Power uint32
}

func (this *Group) Get() error {
	return gossdb.C().GetObject(data.USER_GROUP+this.Id, this)
}
func (this *Group) MultiHsetSave(kvs map[string]interface{}) error {
	return gossdb.C().MultiHset(data.USER_GROUP+this.Id, kvs)
}
func (this *Group) Save() error {
	err := gossdb.C().Hset(data.USER_GROUP, this.Id, this.Id)
	if err == nil {
		return gossdb.C().PutObject(data.USER_GROUP+this.Id, this)
	}
	return err
}
func DeleteGroup(c *gin.Context) {

}
func CreateGroup(c *gin.Context) {

}
func EditGroup(c *gin.Context) {

}
func ListGroup(c *gin.Context) {

}
func ExistGroupName() bool {
	return false
}
