package gossdb

import (
	"sync"
	"testing"
)

func Test(t *testing.T) {
	var w sync.WaitGroup
	for i := 0; i < 1000; i++ {
		w.Add(1)
		go func() {
			v := C().Ping()
			t.Log(v)
			w.Add(-1)
		}()
	}
	w.Wait()
}
