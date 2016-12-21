#!/bin/bash
export GOPATH=`pwd`
export GOARCH=amd64
export GOOS=linux
cd bin
go build -o admins -ldflags "-w -s" ../src/admin.go



