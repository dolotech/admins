package operation

import (
	"basic/ssdb/gossdb"
	"testing"
)

func Test_login(t *testing.T) {
	gossdb.Connect("119.29.24.17", 8888, 1)
	list, size, err := getLoginRecord("60751", 0, 50)
	t.Log(list, size, err)
}
