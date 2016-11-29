package data

import "github.com/labstack/echo"

type H map[string]interface{}

func GetCurrentSession(c echo.Context) *Session {
	cookie, err := c.Cookie("login")
	if err == nil && cookie != nil && len(cookie.Value) > 0 {
		return Sessions.Get(cookie.Value)
	}
	return nil
}
func GetCurrentUserID(c echo.Context) string {
	se := GetCurrentSession(c)
	if se != nil {
		return se.Username
	}
	return ""
}
func GetCurrentUser(c echo.Context) *Admin {
	id := GetCurrentUserID(c)
	if id == "" {
		return nil
	}
	user := &Admin{Id: id}
	if err := user.Get(); err != nil {
		return nil
	}
	return user
}
