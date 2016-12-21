package admincall

import (
	"basic/rpc"
	"data"

	"github.com/golang/glog"
)

var Client *rpc.RPCClient

func Init(addr string) {
	Client = rpc.CreateClient(addr)
}

type EmailReceiverArgs struct {
	Userid []string //  玩家ID
	Data   *data.DataPostbox
}

func (this *EmailReceiverArgs) Call() bool {
	ok := true
	Client.Call("EmailReceiver.Receive", this, &ok)
	glog.Infoln("=========", ok)
	return ok
}
