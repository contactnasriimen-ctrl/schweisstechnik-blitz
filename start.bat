@echo off
rem Schweisstechnik Blitz - statische Version lokal (baut zuerst aus dem Theme)
cd /d "%~dp0"
set BLITZ_DEV=1
set PHP=C:\xampp\php\php.exe
if not exist "%PHP%" set PHP=php
"%PHP%" tools\build-static.php
echo Website laeuft auf http://localhost:8091  (Fenster schliessen zum Beenden)
start "" http://localhost:8091
"%PHP%" -S localhost:8091 -t static
