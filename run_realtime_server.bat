@echo off
TITLE Visitor System Realtime Server
COLOR 0A
ECHO ========================================================
ECHO   STARTING VISITOR SYSTEM REALTIME SERVER
ECHO ========================================================
ECHO.
ECHO This window must remain open for realtime notifications
ECHO (Admin Dashboard / Visitor Status Updates) to work.
ECHO.
ECHO Local Server: http://localhost:3005
ECHO.

cd realtime

:START
ECHO [%TIME%] Starting Node.js server...
node server.js
ECHO.
ECHO [%TIME%] Server crashed or stopped. Restarting in 5 seconds...
timeout /t 5
GOTO START
