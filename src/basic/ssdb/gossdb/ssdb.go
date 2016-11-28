package gossdb

import (
	"bytes"
	"fmt"
	"net"
	"strconv"
	"time"
)

type SSDBClient struct {
	sock     *net.TCPConn
	recv_buf bytes.Buffer
	send_buf bytes.Buffer
	IP       string
	Port     int
}

func (c *SSDBClient) Connect() error {
	addr, err := net.ResolveTCPAddr("tcp", fmt.Sprintf("%s:%d", c.IP, c.Port))
	if err != nil {
		return err
	}
	sock, err := net.DialTCP("tcp", nil, addr)
	if err != nil {
		return err
	}

	//sock.SetReadDeadline(time.Now().Add(time.Minute * 15))
	sock.SetReadDeadline(time.Now().Add(time.Second * 15))
	c.sock = sock
	return nil
}
func (c *SSDBClient) Write(args ...interface{}) error {
	return c.Send(args)
}

func (c *SSDBClient) Do(args ...interface{}) ([]Value, error) {
	err := c.Send(args)
	if err != nil {
		return nil, err
	}
	return c.Recv()
}

var ok = []byte("ok")

func (c *SSDBClient) Set(key string, val string) (interface{}, error) {
	resp, err := c.Do("set", key, val)
	if err != nil {
		return nil, err
	}
	if len(resp) == 2 && resp[0][0] == ok[0] && resp[0][1] == ok[1] {
		return true, nil
	}
	return nil, fmt.Errorf("bad response")
}

// TODO: Will somebody write addition semantic methods?
func (c *SSDBClient) Get(key string) (interface{}, error) {
	resp, err := c.Do("get", key)
	if err != nil {
		return nil, err
	}
	if len(resp) == 2 && resp[0][0] == ok[0] && resp[0][1] == ok[1] {
		return resp[1], nil
	}
	if string(resp[0]) == "not_found" {
		return nil, nil
	}
	return nil, fmt.Errorf("bad response")
}

func (c *SSDBClient) Del(key string) (interface{}, error) {
	resp, err := c.Do("del", key)
	if err != nil {
		return nil, err
	}
	if len(resp) == 1 && resp[0][0] == ok[0] && resp[0][1] == ok[1] {
		return true, nil
	}
	return nil, fmt.Errorf("bad response")
}

func (c *SSDBClient) Send(args []interface{}) error {
	c.send_buf.Reset()
	for _, arg := range args {
		var s string
		switch arg := arg.(type) {
		case string:
			s = arg
		case []byte:
			s = string(arg)
		case [][]byte:
			for _, s := range arg {
				c.send_buf.WriteString(fmt.Sprintf("%d", len(s)))
				c.send_buf.WriteByte('\n')
				c.send_buf.Write(s)
				c.send_buf.WriteByte('\n')
			}
			continue
		case []string:
			for _, s := range arg {
				c.send_buf.WriteString(fmt.Sprintf("%d", len(s)))
				c.send_buf.WriteByte('\n')
				c.send_buf.WriteString(s)
				c.send_buf.WriteByte('\n')
			}
			continue
		case []uint32:
			for _, num := range arg {
				s := fmt.Sprintf("%d", num)
				c.send_buf.WriteString(fmt.Sprintf("%d", len(s)))
				c.send_buf.WriteByte('\n')
				c.send_buf.WriteString(s)
				c.send_buf.WriteByte('\n')
			}
			continue
		case int:
			s = fmt.Sprintf("%d", arg)
		case int32:
			s = fmt.Sprintf("%d", arg)
		case int64:
			s = fmt.Sprintf("%d", arg)
		case uint32:
			s = fmt.Sprintf("%d", arg)
		case uint64:
			s = fmt.Sprintf("%d", arg)
		case float64:
			s = fmt.Sprintf("%f", arg)
		case bool:
			if arg {
				s = "1"
			} else {
				s = "0"
			}
		case nil:
			s = ""
		default:
			return fmt.Errorf("bad arguments: %v", arg)
		}
		c.send_buf.WriteString(fmt.Sprintf("%d", len(s)))
		c.send_buf.WriteByte('\n')
		c.send_buf.WriteString(s)
		c.send_buf.WriteByte('\n')
	}
	c.send_buf.WriteByte('\n')
	_, err := c.sock.Write(c.send_buf.Bytes())
	return err
}

func (c *SSDBClient) Recv() ([]Value, error) {
	var tmp [8192]byte
	for {
		n, err := c.sock.Read(tmp[0:])
		if err != nil {
			return nil, err
		}
		c.recv_buf.Write(tmp[0:n])
		resp := c.parse()
		if resp == nil || len(resp) > 0 {
			return resp, nil
		}
	}
}

func (c *SSDBClient) parse() []Value {
	resp := []Value{}
	buf := c.recv_buf.Bytes()
	var idx, offset int
	idx = 0
	offset = 0

	for {
		idx = bytes.IndexByte(buf[offset:], '\n')
		if idx == -1 {
			break
		}
		p := buf[offset : offset+idx]
		offset += idx + 1
		//fmt.Printf("> [%s]\n", p);
		if len(p) == 0 || (len(p) == 1 && p[0] == '\r') {
			if len(resp) == 0 {
				continue
			} else {
				c.recv_buf.Next(offset)
				return resp
			}
		}

		size, err := strconv.Atoi(string(p))
		if err != nil || size < 0 {
			return nil
		}
		if offset+size >= c.recv_buf.Len() {
			break
		}

		var v Value = buf[offset : offset+size]
		resp = append(resp, v)
		offset += size + 1
	}

	return resp
}

// Close The SSDBClient Connection
func (c *SSDBClient) Close() error {
	if c.sock != nil {
		return c.sock.Close()
	}
	return nil
}
