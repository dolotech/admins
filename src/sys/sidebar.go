package sys

/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-03-18 10:16
 * Filename      : users.go
 * Description   :
 * *******************************************************/

import (
	"net/http"

	"github.com/gin-gonic/gin"
)

//SidebarList 边栏列表
type SidebarList struct {
	Name  string        `json:"name"`
	Items []SidebarItem `json:"items"`
}

// SidebarItem 边栏列表项
type SidebarItem struct {
	Path string `json:"path"`
	Name string `json:"name"`
}

// Sidebar 获取边栏列表数据
func Sidebar(c *gin.Context) {
	items := []SidebarList{
		{
			"玩家管理",
			[]SidebarItem{{"/roles/list.html", "玩家列表"},
				{"/roles/listonline.html", "在线玩家"}},
		},
		{
			"运营管理",
			[]SidebarItem{{"/operation/provide.html", "道具/钻石发放"},
				{"/operation/providerecord.html", "发放记录"},
				{"/operation/privaterecord.html", "私人局记录"},
				{"/operation/matchrecord.html", "比赛场记录"},
				{"/operation/normalrecord.html", "金币场记录"}},
		},
		{
			"订单管理",
			[]SidebarItem{{"/users/list.html", "下单列表"},
				{"/users/create.html", "充值列表"}},
		},
	}
	c.JSON(http.StatusOK, gin.H{"status": "ok", "data": items})
}
