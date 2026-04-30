'' =====================================================
'' Pecosol - Iniciar Chatbot Automáticamente (VBS)
'' =====================================================
'' Script invisible para iniciar el servidor Python en segundo plano
'' Copiar a: c:\xampp\htdocs\pecosol\python_api\AutoStart-Chatbot.vbs

Set objShell = CreateObject("WScript.Shell")
strPythonAPI = objShell.CurrentDirectory & "\python_api"

'' Verificar si el puerto 8000 está disponible
Set objWMI = GetObject("winmgmts:")
Set colProcesses = objWMI.ExecQuery("Select * From Win32_Process Where Name = 'python.exe'")

'' Si Python ya está corriendo, no hacer nada
If colProcesses.Count > 0 Then
    WScript.Quit 0
End If

'' Iniciar el servidor en segundo plano (invisible)
objShell.Run "cmd.exe /c cd /d """ & strPythonAPI & """ && python main.py", 0, False

'' Esperar 2 segundos para que inicie
WScript.Sleep 2000
