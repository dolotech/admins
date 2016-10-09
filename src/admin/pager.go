/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-07-28 10:16
 * Filename      : pager.go
 * Description   :
 * *******************************************************/
package admin

import (
	"math"
	"strconv"

	"github.com/gin-gonic/gin"
	"github.com/golang/glog"
)

type pager struct {
	Page  int    // 当前第几页
	Limit int    // 当前页数量
	Act   string // prev | next
	Prev  int    // 上一页
	Next  int    // 下一页
	First int    // 第一页
	Last  int    // 最后面
	List  []int  // 多少页
	Size  uint32 // 总数量
	Sid   string //
	Eid   string //
}

func (p *pager) GetEnd() int {
	glog.Infoln(p.Size, p.Page, p.Limit)
	if p.Size >= uint32((p.Page)*p.Limit) {
		return p.Page * p.Limit
	}
	return int(p.Size)
}
func (p *pager) GetStart() int {
	glog.Infoln(p.Size, p.Page, p.Limit)
	if p.Size >= uint32((p.Page-1)*p.Limit) {
		return (p.Page - 1) * p.Limit
	}
	return 0
}

// &pager{}.SetPager(page_s, limit_s, act)
func (p *pager) SetPager(page_s, limit_s, act string) {
	page, _ := strconv.Atoi(page_s) // int
	if act == "prev" {
		page = page - 1
	} else if act == "next" {
		page = page + 1
	}
	limit, _ := strconv.Atoi(limit_s) // int
	if limit < 1 {
		limit = LIMIT
	}

	p.Page = page

	p.First = 1
	p.Limit = limit
	p.Prev = page - 1
	p.Next = page + 1
	if p.Page > int(math.Ceil(float64(p.Size/uint32(p.Limit)))) {
		p.Page = int(math.Ceil(float64(p.Size / uint32(p.Limit))))
	}
	if p.Page < 1 {
		p.Page = 1
	}

}

func (p *pager) SetSize(size uint32) {
	p.Size = size
	p.List = []int{}
	p.Last = int(size / uint32(p.Limit))
	if p.Last < 1 {
		p.Last = 1
	}
	for i := 1; i <= p.Last; i++ {
		p.List = append(p.List, i)
	}
}

func (p *pager) GetPager(c *gin.Context) {
	start_id := c.Query("start_id")
	end_id := c.Query("end_id")
	page_s := c.Query("page") // string
	act_s := c.Query("act")   // string
	limit_s := c.Query("limit")
	p.Sid = start_id
	p.Eid = end_id
	p.SetPager(page_s, limit_s, act_s)
}
