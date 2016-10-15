package main

import (
	"admin"
	"basic/ssdb/gossdb"
	"data"
	"flag"
	"net/http"
	"runtime/debug"
	"time"

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
				c.HTML(http.StatusNotFound, "admin-404.html", gin.H{
					"message": "",
				})

				glog.Errorln(string(debug.Stack()))
			}
		}()
		//	session := sessions.Default(c)
		//	token := session.Get("username")

		//	uri := c.Request.RequestURI

		//	if strings.EqualFold(uri, "/users/login") || (len(uri) > 8 && uri[:8] == "/assets/") {
		//		c.Next()
		//		return
		//	}

		//	if token == nil || token == "" {
		//		glog.Infoln("token is nil")
		//		c.Redirect(http.StatusMovedPermanently, "/users/login")
		//		//c.Abort()
		//		return
		//	}
		c.Next()
	}

}

// 页面路由
func Router(r *gin.Engine) {

	r.POST("/roles/list", admin.Roles.List)
	r.POST("/roles/edit", admin.Roles.Edit)

	r.POST("/users/login", admin.Users.Authenticate)
	r.POST("/sidebar", admin.Sidebar)

	r.Static("/assets", "AmazeUI/assets")
	r.Static("/users", "AmazeUI/users")
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
