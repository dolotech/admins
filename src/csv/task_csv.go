package csv

import (
	"basic/csv"
	"io/ioutil"
	"os"

	"github.com/golang/glog"
)

var taskMap map[uint32]TaskData

type TaskData struct {
	Id      uint32 `csv:"id"`
	Kind    uint32 `csv:"kind"`
	Count   uint32 `csv:"count"`
	Type    uint32 `csv:"type"`
	Next    uint32 `csv:"next"`
	Rewards uint32 `csv:"rewards"` // 任务奖励id
	Status  uint32 `csv:"status"`  // 任务激活状态1:激活
}

var task []TaskData

func GetTaskData() []TaskData {
	return task
}

func GetTask(id uint32) *TaskData {
	task, ok := taskMap[id]
	if ok {
		return &task
	} else {
		return nil
	}
}

func init() {
	f, err := os.Open("./csv/task.csv")
	if err != nil {
		panic(err)
	}
	defer f.Close()

	data, err := ioutil.ReadAll(f)
	if err != nil {
		panic(err)
	}
	err = csv.Unmarshal(data, &task)
	if err != nil {
		panic(err)
	}
	taskMap = make(map[uint32]TaskData)
	for _, v := range task {
		taskMap[v.Id] = v
	}
	glog.Infoln("任务表：", len(taskMap))
}
