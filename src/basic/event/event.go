// 简单的事件派发类,支持线程安全，注意：不能嵌套监听事件，即事件监听方法类调用监听
package event

import "sync"

type Handler func(string, interface{})

type IDispath interface {
	Dispatch(eventtype string, args interface{})
	Listen(eventtype string, hander Handler)
	ListenOnce(eventtype string, hander Handler)
}
type listenArgs struct {
	eventType string
	once      bool
	hander    Handler
}
type Dispatcher struct {
	sync.RWMutex
	listenMsg map[string][]*listenArgs
}

// 派发自定义类型事件，附带一个参数
func (this *Dispatcher) Dispatch(eventtype string, args interface{}) {
	defer this.RUnlock()
	this.RLock()
	if this.listenMsg != nil {
		if list, ok := this.listenMsg[eventtype]; ok {
			tmp := []*listenArgs{}
			for _, v := range list {
				v.hander(eventtype, args)
				if !v.once {
					tmp = append(tmp, v)
				}
			}
			this.listenMsg[eventtype] = tmp
		}
	}
}

//  监听事件
func (this *Dispatcher) Listen(eventtype string, hander Handler) {
	defer this.Unlock()
	this.Lock()

	if this.listenMsg == nil {
		this.listenMsg = make(map[string][]*listenArgs)
	}

	this.listenMsg[eventtype] = append(this.listenMsg[eventtype], &listenArgs{eventType: eventtype, hander: hander})

}

//  监听一次指定类型事件, 接受完事件移除监听
func (this *Dispatcher) ListenOnce(eventtype string, hander Handler) {
	defer this.Unlock()
	this.Lock()
	if this.listenMsg == nil {
		this.listenMsg = make(map[string][]*listenArgs)
	}

	this.listenMsg[eventtype] = append(this.listenMsg[eventtype], &listenArgs{once: true, eventType: eventtype, hander: hander})
}
