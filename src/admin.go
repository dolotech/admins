package main

import (
	"basic/ssdb/gossdb"
	"data"
	"flag"
	"net/http"
	"net/http/httputil"
	"operation"
	"role"
	"runtime/debug"
	"strings"
	"sys"
	"time"
	"user"

	_ "csv"

	"github.com/gin-gonic/gin"
	"github.com/golang/glog"
)

func main() {
	var config string
	flag.StringVar(&config, "conf", "./conf.json", "config path")
	flag.Parse()
	data.LoadConf(config)

	router := gin.New()
	glog.Infoln(data.Conf.Port)
	s := &http.Server{
		Addr:           data.Conf.Port,
		Handler:        router,
		ReadTimeout:    3600 * time.Second,
		WriteTimeout:   3600 * time.Second,
		MaxHeaderBytes: 1 << 20,
	}
	if data.Conf.Mode == 0 {
		gin.SetMode(gin.ReleaseMode)
	} else {
		gin.SetMode(gin.DebugMode)
	}
	conndb()
	user.InitGroup()
	//	router.Use(Recovery())
	router.Use(authorityMiddleware())
	Router(router)
	glog.Infoln("running success!")
	s.ListenAndServe()
}
func Recovery() gin.HandlerFunc {
	return RecoveryWithWriter()
}

func RecoveryWithWriter() gin.HandlerFunc {
	return func(c *gin.Context) {
		defer func() {
			if err := recover(); err != nil {
				httprequest, _ := httputil.DumpRequest(c.Request, false)
				glog.Errorln(string(httprequest), err, string(debug.Stack()))
				c.AbortWithStatus(500)
			}
		}()
		c.Next()
	}
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
		if c.Request.Method == "GET" {
			//	c.Next()
			return
		}

		if strings.EqualFold(uri, "/users/login") {
			c.Next()
			return
		}

		token, err := c.Cookie("login")
		glog.Infoln(token, uri, err)
		if err != nil || token == "" {
			c.JSON(http.StatusOK, gin.H{"status": "fail", "errorcode": 100, "msg": "未登陆"})
			c.Abort()
			return
		}

		loginses := &data.Session{}
		err = loginses.Get(token)
		glog.Infoln(err, token, uri)
		if err != nil {
			c.JSON(http.StatusOK, gin.H{"status": "fail", "errorcode": 100, "msg": "未登陆"})
			c.Abort()
			return
		}

		now := uint32(time.Now().Unix())
		glog.Infoln(loginses.Expire, token, uri, now)
		if loginses.Expire < now {
			c.JSON(http.StatusOK, gin.H{"status": "fail", "errorcode": 100, "msg": "未登陆"})
			c.Abort()
			return
		}
		c.Next()
	}
}

func Root(c *gin.Context) {
	c.Redirect(http.StatusMovedPermanently, "/roles/list.html")
}

// 页面路由
func Router(r *gin.Engine) {

	r.POST("/roles/list", role.List)
	r.GET("/", Root)
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
	r.POST("/group/list", user.Groups)
	r.POST("/group/delete", user.DeleteGroup)

	r.POST("/roles/listonline", role.ListOnline) //  在线玩家列表

	r.POST("/operation/normalrecord", operation.NormalRecord)         //  金币场牌局记录
	r.POST("/operation/cardrecord", operation.CardRecode)             //  金币场牌打牌记录
	r.POST("/operation/issueprops", operation.IssueProps)             //
	r.POST("/operation/issuelist", operation.IssuePropsList)          //
	r.POST("/operation/loginrecord", operation.LoginRecord)           //
	r.POST("/operation/roomcreaterecord", operation.RoomCreateRecord) // 私人房创建记录

	r.Static("/assets", "AmazeUI/assets")

	r.Static("/users", "AmazeUI/users")
	r.Static("/operation", "AmazeUI/operation")
	r.Static("/roles", "AmazeUI/roles")
	r.Static("/room", "AmazeUI/room")
}

// 链接数据库
func conndb() {
	glog.Infoln("Config: ", data.Conf)
	gossdb.Connect(data.Conf.Db.Ip, data.Conf.Db.Port, data.Conf.Db.Thread)
	defer glog.Flush()
}
