package resource

import (
	"data"
	"testing"

	"github.com/seefan/gossdb"
)

func Test_1(t *testing.T) {
	err := gossdb.C().Hset(data.KEY_USER+"60229", "Coin", 99999)
	if err == nil {
		gossdb.C().Zset(data.KEY_TOTAL_COIN, "60229", int64(99999))
	}

}
