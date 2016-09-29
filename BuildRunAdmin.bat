@echo Setting up environment for GOPATH from %~dp0.
@set GOPATH=%~dp0


cd bin
del /f admin.exe
taskkill /F /IM admin.exe

go build  -o admin.exe -ldflags "-w -s" ../src/admin.go
admin.exe  -alsologtostderr -log_dir="log"
pause
