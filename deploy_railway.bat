@echo off
REM Despliega los cambios a Railway usando el script PowerShell.
REM Uso:
REM   deploy_railway.bat <WEB_SERVICE_ID> <CHATBOT_SERVICE_ID> [Commit Message]

if "%~1"=="" (
  echo ERROR: Debes proporcionar el Web Service ID.
  echo Uso: deploy_railway.bat ^<WEB_SERVICE_ID^> ^<CHATBOT_SERVICE_ID^> [Commit Message]
  exit /b 1
)
if "%~2"=="" (
  echo ERROR: Debes proporcionar el Chatbot Service ID.
  echo Uso: deploy_railway.bat ^<WEB_SERVICE_ID^> ^<CHATBOT_SERVICE_ID^> [Commit Message]
  exit /b 1
)

set "WEB_SERVICE_ID=%~1"
set "CHATBOT_SERVICE_ID=%~2"
shift
shift
set "COMMIT_MESSAGE=%*"
if "%COMMIT_MESSAGE%"=="" set "COMMIT_MESSAGE=Deploy local changes to Railway"

powershell -NoProfile -ExecutionPolicy Bypass -Command "& { . '%~dp0scripts\deploy_railway.ps1' -WebServiceId '%WEB_SERVICE_ID%' -ChatbotServiceId '%CHATBOT_SERVICE_ID%' -CommitMessage '%COMMIT_MESSAGE%' }"
