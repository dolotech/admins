/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-03-18 10:16
 * Filename      : users.go
 * Description   :
 * *******************************************************/
package admin

import (
	"net/http"

	"github.com/gin-gonic/gin"
)

type SidebarList struct {
	Name  string        `json:"name"`
	Items []SidebarItem `json:"items"`
}
type SidebarItem struct {
	Path string `json:"path"`
	Name string `json:"name"`
}

func Sidebar(c *gin.Context) {
	items := []SidebarList{
		{
			"权限管理",
			[]SidebarItem{{"/users/list.html", "管理员列表"},
				{"/users/create.html", "新增管理员"},
			},
		},
		{
			"玩家管理",
			[]SidebarItem{{"/roles/list.html", "玩家列表"},
				{"/users/online.html", "在线列表"},
				{"/users/ative.html", "活跃玩家"}},
		},
		{
			"运营管理",
			[]SidebarItem{{"/users/provide.html", "道具/钻石发放"},
				{"/users/providerecord.html", "发放记录"}},
		},
		{
			"订单管理",
			[]SidebarItem{{"/users/list.html", "下单列表"},
				{"/users/create.html", "充值列表"}},
		},
	}

	//data, _ := json.Marshal(items)
	c.JSON(http.StatusOK, gin.H{"status": "ok", "data": items})
}
