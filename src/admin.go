package main

import (
	"basic/ssdb/gossdb"
	"data"
	"flag"
	"net/http"
	"operation"
	"role"
	"runtime/debug"
	"strings"
	"sys"
	"time"
	"user"

	"github.com/gin-gonic/contrib/sessions"
	"github.com/gin-gonic/gin"
	"github.com/golang/glog"
)

func main() {
	router := gin.New()
	s := &http.Server{
		Addr:           ":80",
		Handler:        router,
		ReadTimeout:    3600 * time.Second,
		WriteTimeout:   3600 * time.Second,
		MaxHeaderBytes: 1 << 20,
	}
	conndb()
	store := sessions.NewCookieStore([]byte("secret"))
	router.Use(sessions.Sessions("mysession", store))
	router.Use(authorityMiddleware())
	Router(router)
	s.ListenAndServe()
}

// 权限验证
func authorityMiddleware() gin.HandlerFunc {
	return func(c *gin.Context) {

		defer func() {
			if r := recover(); r != nil {
				c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "服务器器出错"})
				glog.Errorln(string(debug.Stack()))
			}
		}()

		c.Writer.Header().Set("Access-Control-Allow-Origin", "http://localhost")
		c.Writer.Header().Set("Access-Control-Max-Age", "86400")
		c.Writer.Header().Set("Access-Control-Allow-Methods", "POST, GET, OPTIONS, PUT, DELETE, UPDATE")
		c.Writer.Header().Set("Access-Control-Allow-Headers", "X-Requested-With, Content-Type, Origin, Authorization, Accept, Client-Security-Token, Accept-Encoding, x-access-token")
		c.Writer.Header().Set("Access-Control-Expose-Headers", "Content-Length")
		c.Writer.Header().Set("Access-Control-Allow-Credentials", "true")
		uri := c.Request.RequestURI
		session := sessions.Default(c)
		token := session.Get("loginsession")

		if c.Request.Method == "GET" {
			c.Next()
			return
		}
		if strings.EqualFold(uri, "/users/login") {
			glog.Infoln(token, uri)
			c.Next()
			return
		}
		if strings.EqualFold(uri, "/users/login.html") {
			glog.Infoln(token, uri)
			c.Next()
			return
		}
		//	glog.Infoln("===============", uri)
		//	if len(uri) > 8 && uri[:8] == "/assets/" {
		//		c.Next()
		//		return
		//	}

		//	glog.Infoln("===============", uri)
		//	if strings.EqualFold(uri, "/users/login.html") || strings.EqualFold(uri, "/users/login") {
		//		//glog.Infoln("token is nil")
		//		//c.Redirect(http.StatusMovedPermanently, "/users/login.html")
		//		//c.Abort()
		//		c.Next()
		//		return
		//	}

		glog.Infoln(token, uri)
		if token == nil || token == "" {
			//	c.Redirect(http.StatusMovedPermanently, "/users/login.html")
			//	c.Abort()

			c.JSON(http.StatusOK, gin.H{"status": "fail", "msg": "你未登陆"})
			return
		}
		c.Next()
	}

}

// 页面路由
func Router(r *gin.Engine) {

	r.POST("/roles/list", role.List)
	r.POST("/roles/search", role.Search)
	r.POST("/roles/edit", role.Edit)

	r.POST("/users/login", user.Login)
	r.POST("/users/logout", user.Logout)
	r.POST("/sidebar", sys.Sidebar)

	r.POST("/users/create", user.Create)
	r.POST("/users/edit", user.Edit)
	r.POST("/users/list", user.List)
	r.POST("/users/delete", user.Delete)

	r.POST("/group/create", user.CreateGroup)
	r.POST("/group/edit", user.EditGroup)
	r.POST("/group/list", user.ListGroup)
	r.POST("/group/delete", user.DeleteGroup)

	r.POST("/roles/listonline", role.ListOnline) //  在线玩家列表

	r.POST("/operation/normalrecord", operation.NormalRecord) //  金币场牌局记录
	r.POST("/operation/issueprops", operation.IssueProps)     //
	r.POST("/operation/issuelist", operation.IssuePropsList)  //

	r.Static("/assets", "AmazeUI/assets")

	r.Static("/users", "AmazeUI/users")
	r.Static("/operation", "AmazeUI/operation")
	r.Static("/roles", "AmazeUI/roles")
}

// 链接数据库
func conndb() {
	var config string
	flag.StringVar(&config, "conf", "./conf.json", "config path")
	flag.Parse()
	data.LoadConf(config)
	glog.Infoln("Config: ", data.Conf)
	gossdb.Connect(data.Conf.Db.Ip, data.Conf.Db.Port, data.Conf.Db.Thread)
	defer glog.Flush()
}
