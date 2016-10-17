package main

import (
	"basic/ssdb/gossdb"
	"data"
	"flag"
	"net/http"
	"role"
	"runtime/debug"
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
				c.HTML(http.StatusNotFound, "admin-404.html", gin.H{
					"message": "",
				})

				glog.Errorln(string(debug.Stack()))
			}
		}()
		//	session := sessions.Default(c)
		//	token := session.Get("loginsession")

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

	r.POST("/roles/list", role.List)
	r.POST("/roles/search", role.Search)
	r.POST("/roles/edit", role.Edit)

	r.POST("/users/login", user.Login)
	r.POST("/sidebar", sys.Sidebar)

	r.POST("/users/create", user.Create)
	r.POST("/users/edit", user.Edit)
	r.POST("/users/list", user.List)
	r.POST("/users/delete", user.Delete)

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
