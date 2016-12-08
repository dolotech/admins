@echo Setting up environment for GOPATH from %~dp0.
@set GOPATH=%~dp0


@set GOPATH=%~dp0
set GOARCH=amd64
set GOOS=linux



go build  -o ./bin/admins -ldflags "-w -s" ./src/admin.go


pause
