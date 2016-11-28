/**********************************************************
 * Author        : Michael
 * Email         : dolotech@163.com
 * Last modified : 2016-01-23 10:24
 * Filename      : packet.go
 * Description   :	ssdb数据库连接对象池
 * *******************************************************/

package gossdb

import (
	"log"
	"time"
)

var pool chan *Client
var maxthread int

func Connect(addr string, port int, thread int) {
	if thread < 1 {
		thread = 5
	}
	maxthread = thread
	pool = make(chan *Client, thread)
	for i := 0; i < thread; i++ {
		c := &Client{
			IP:   addr,
			Port: port,
			Pool: pool,
		}
		err := c.Start()
		if err != nil {
			log.Fatal(err)
		}
		pool <- c
	}
	go loopPing()
}
func ping() {
	for i := 0; i < maxthread; i++ {
		select {
		case c := <-pool:
			_, err := c.Info()
			if err != nil {
			}
		default:
			return
		}
	}
}
func loopPing() {
	for {
		select {
		case <-time.After(time.Second * 9):
			//	case <-time.After(time.Minute * 5):
			ping()
		}
	}
}

func C() *Client {
	c := <-pool
	return c
}
