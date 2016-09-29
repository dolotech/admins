package event

import "testing"

func Test_event(t *testing.T) {
	dispatch := &Dispatcher{}
	f := func(eTyte string, args interface{}) {
		t.Log(args)
	}
	dispatch.Listen("hello", f)
	dispatch.ListenOnce("hello1", func(eType string, args interface{}) {
		t.Log(args)
		//	dispatch.ListenOnce("hello2", func(eType string, args interface{}) {
		//		t.Log(args)
		//	})

	})
	//dispatch.RemoveListen("hello", f)
	dispatch.Dispatch("hello", "你好")
	dispatch.Dispatch("hello", "你好")

	dispatch.Dispatch("hello1", "你好")
	dispatch.Dispatch("hello1", "你好")
}
