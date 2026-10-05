@echo off
rem Schweisstechnik Blitz - lokaler Testserver
cd /d "%~dp0"
set BLITZ_DEV=1
set PHP=C:
mpp\php\php.exe
if not exist "%PHP%" set PHP=php
echo Website laeuft auf http://localhost:8091  (Fenster schliessen zum Beenden)
start "" http://localhost:8091
"%PHP%" -S localhost:8091
