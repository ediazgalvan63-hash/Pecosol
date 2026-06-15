@echo off
cd /d C:\xampp\htdocs\pecosol
C:\xampp\php\php.exe scripts\keep_three_users.php --sync > scripts\keep_three_users_out.txt 2>&1
echo EXIT_CODE=%ERRORLEVEL%>> scripts\keep_three_users_out.txt
