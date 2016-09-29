package utils

import (
	"math/rand"
	"time"
)

var o *rand.Rand = rand.New(rand.NewSource(TimestampNano()))

func RandInt64() (r int64) {
	return (o.Int63())
}

func RandInt32() (r int32) {
	return (o.Int31())
}

func RandUint32() (r uint32) {
	return (o.Uint32())
}

func RandInt64N(n int64) (r int64) {
	return (o.Int63n(n))
}

func RandInt32N(n int32) (r int32) {
	return (o.Int31n(n))
}

var randomChan chan uint32

func randUint32() {
	randomChan = make(chan uint32, 1024)
	go func() {
		var numstr uint32
		for {
			numstr = RandUint32()
			select {
			case randomChan <- numstr:
			}
			<-time.After(time.Millisecond * 100)
		}
	}()
}

func GetRandUint32() uint32 {
	return <-randomChan
}
