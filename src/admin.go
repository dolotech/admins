package main

import (
	"admin"
	"basic/ssdb/gossdb"
	"data"
	"flag"
	"net/http"
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
	//store, _ := sessions.NewRedisStore([]byte("secret"))
	store := sessions.NewCookieStore([]byte("secret"))
	router.Use(sessions.Sessions("mysession", store))
	//	router.Use(authorityMiddleware())
	Router(router)
	s.ListenAndServe()
}

// 权限验证
func authorityMiddleware() gin.HandlerFunc {
	return func(c *gin.Context) {
		glog.Infoln(c.Request.URL.Path, c.Request.Method)

		if c.Request.URL.Path != "/users/login" {
			session := sessions.Default(c)
			logined := session.Get("username")
			glog.Infoln("cookie: ", logined, "Path: ", c.Request.URL.Path, logined)
			if logined == nil || logined == "" {
				c.Redirect(http.StatusMovedPermanently, "/users/login")
				glog.Infoln("cookie 为空", session.Get("username"))
			} else {
				c.Next()
			}
		}
	}
}

// 页面路由
func Router(r *gin.Engine) {

	//r.Use(authorityMiddleware())

	r.GET("/", admin.Roles.List)
	r.GET("/file", admin.Files.List)
	r.POST("/file", admin.Files.Upload)
	r.DELETE("/file", admin.Files.Delete)
	r.GET("/file/indexdown", admin.Files.IndexDown)
	r.GET("/file/indexup", admin.Files.IndexUp)

	r.GET("/roles/list", admin.Roles.List)
	r.POST("/roles/edit", admin.Roles.Edit)
	r.GET("/roles/edituser", admin.Roles.EditUser)

	r.GET("/users/login", admin.Users.Login)
	r.POST("/users/login", admin.Users.Authenticate)
	r.GET("/users/logout/", admin.Users.Logout)
	r.GET("/users/list", admin.Users.List)
	r.GET("/users/create", admin.Users.Create)
	r.POST("/users/created", admin.Users.Created)
	r.POST("/users/search", admin.Users.Search)
	r.POST("/users/edit", admin.Users.Edit)
	r.POST("/users/group_list", admin.Users.GroupList)
	r.POST("/users/group_edit", admin.Users.GroupEdit)
	r.POST("/users/setpwd", admin.Users.Setpwd)
	r.GET("/users/setpasswd", admin.Users.Setpasswd)
	r.POST("/users/register", admin.Users.RegisterDemo)

	r.LoadHTMLGlob("AmazeUI/*/*.html")
	r.Static("/assets", "AmazeUI/assets")

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
