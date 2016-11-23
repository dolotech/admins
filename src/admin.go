package main

import (
	"basic/ssdb/gossdb"
	"data"
	"errors"
	"flag"
	"net/http"
	"operation"
	"role"
	"user"

	_ "csv"

	"github.com/golang/glog"
	"github.com/labstack/echo"
	"github.com/labstack/echo/middleware"
)

func loginMiddlewareStatic(next echo.HandlerFunc) echo.HandlerFunc {
	return func(c echo.Context) error {
		cookie, err := c.Cookie("login")
		if err != nil || cookie == nil || len(cookie.Value) <= 0 {
			c.Request().Header.Add("Cache-Control", "no-cache")
			err := c.Redirect(http.StatusTemporaryRedirect, "/login/login.html")
			return err
		}
		if data.Sessions.Get(cookie.Value) == nil {
			c.Request().Header.Add("Cache-Control", "no-cache")
			err := c.Redirect(http.StatusTemporaryRedirect, "/login/login.html")
			return err
		}

		return next(c)
	}
}
func loginMiddleware(next echo.HandlerFunc) echo.HandlerFunc {
	return func(c echo.Context) error {
		cookie, err := c.Cookie("login")
		if err != nil || cookie == nil || len(cookie.Value) <= 0 {
			c.JSON(http.StatusOK, data.H{"status": "fail", "errorcode": 100, "msg": "未登陆"})
			return errors.New("未登陆")
		}
		if data.Sessions.Get(cookie.Value) == nil {
			c.JSON(http.StatusOK, data.H{"status": "fail", "errorcode": 100, "msg": "未登陆"})
			return errors.New("未登陆")
		}
		return next(c)
	}
}

func main() {
	var config string
	flag.StringVar(&config, "conf", "./conf.json", "config path")
	flag.Parse()
	data.LoadConf(config)

	e := echo.New()
	e.Use(middleware.Recover())

	e.Static("/assets", "AmazeUI/assets")

	e.Static("/users", "AmazeUI/users", loginMiddlewareStatic)
	e.Static("/operation", "AmazeUI/operation", loginMiddlewareStatic)
	e.Static("/roles", "AmazeUI/roles", loginMiddlewareStatic)
	e.Static("/room", "AmazeUI/room", loginMiddlewareStatic)

	e.Static("/login", "AmazeUI/login")

	e.POST("/users/login", user.Login)
	e.POST("/users/logout", user.Logout)

	e.GET("/", func(c echo.Context) error {
		return c.Redirect(http.StatusMovedPermanently, "/roles/list.html")
	})

	e.POST("/roles/list", role.List, loginMiddleware)
	e.POST("/roles/search", role.Search, loginMiddleware)
	e.POST("/roles/edit", role.Edit, loginMiddleware)

	e.POST("/users/create", user.Create, loginMiddleware)
	e.POST("/users/edit", user.Edit, loginMiddleware)
	e.POST("/users/list", user.List, loginMiddleware)
	e.POST("/users/delete", user.Delete, loginMiddleware)

	e.POST("/group/create", user.CreateGroup, loginMiddleware)
	e.POST("/group/edit", user.EditGroup, loginMiddleware)
	e.POST("/group/list", user.Groups, loginMiddleware)
	e.POST("/group/delete", user.DeleteGroup, loginMiddleware)

	e.POST("/roles/listonline", role.ListOnline, loginMiddleware) //  在线玩家列表

	e.POST("/operation/privaterecord", operation.PrivateRecord, loginMiddleware)       // 私人局牌局记录
	e.POST("/operation/matchrecord", operation.MatchRecord, loginMiddleware)           // 比赛场牌局记录
	e.POST("/operation/normalrecord", operation.NormalRecord, loginMiddleware)         //  金币场牌局记录
	e.POST("/operation/cardrecord", operation.CardRecode, loginMiddleware)             //  金币场牌打牌记录
	e.POST("/operation/issueprops", operation.IssueProps, loginMiddleware)             //
	e.POST("/operation/issuelist", operation.IssuePropsList, loginMiddleware)          //
	e.POST("/operation/loginrecord", operation.LoginRecord, loginMiddleware)           //
	e.POST("/operation/roomcreaterecord", operation.RoomCreateRecord, loginMiddleware) // 私人房创建记录

	conndb()

	//for i := 0; i < 1000; i++ {
	//	go func() {
	//		lastID, err := gossdb.C().Get(data.KEY_LAST_USER_ID)
	//		glog.Errorln(err, lastID, data.KEY_LAST_USER_ID)
	//		if err != nil {
	//			glog.Errorln(err, lastID, data.KEY_LAST_USER_ID)
	//		}
	//	}()
	//}

	user.InitAdmin()
	e.Start(data.Conf.Port)
}

// 链接数据库
func conndb() {
	glog.Infoln("Config: ", data.Conf)
	gossdb.Connect(data.Conf.Db.Ip, data.Conf.Db.Port, data.Conf.Db.Thread)
	defer glog.Flush()
}
