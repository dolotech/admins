#!/bin/bash
export GOPATH=`pwd`
cd bin
go build   -o admins.exe -ldflags "-w -s" ../src/admin.go
./admins.exe  -alsologtostderr -log_dir="log"


