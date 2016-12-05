package operation

import (
	"data"
	"net/http"
	"strconv"

	"github.com/golang/glog"
	"github.com/labstack/echo"
)

func GetNotice(c echo.Context) error {
	list := data.GetNotice()
	if len(list) > 0 {
		return c.JSON(http.StatusOK, data.H{"status": "ok", "data": list[0]})
	}
	return c.JSON(http.StatusOK, data.H{"status": "ok", "data": &data.Notice{}})
}
func AddNotice(c echo.Context) error {
	content := c.FormValue("Content")                            // string
	title := c.FormValue("Title")                                // string
	kind, _ := strconv.Atoi(c.FormValue("Kind"))                 // uint32
	expire, _ := strconv.ParseInt(c.FormValue("Expire"), 10, 64) // uint32
	glog.Infoln(expire)
	data.AddNotice(uint32(kind), expire, title, content)

	return c.JSON(http.StatusOK, data.H{"status": "ok"})
}
