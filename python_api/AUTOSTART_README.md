# Iniciar Chatbot Automáticamente

Hay varias formas de configurar el inicio automático del chatbot. Elige la que te convenga:

---

## Opción 1: Windows Task Scheduler (RECOMENDADO)

Esta es la forma más confiable. El servidor se iniciará automáticamente cuando enciendes tu PC.

### Pasos:

1. **Abre el Programador de Tareas:**
   - Presiona `Windows + R`
   - Escribe: `taskschd.msc`
   - Presiona Enter

2. **Crear nueva tarea:**
   - Click derecho en "Biblioteca del Programador de Tareas"
   - Selecciona "Crear tarea..."

3. **Pestaña General:**
   - Nombre: `Pecosol Chatbot`
   - Marca: "Ejecutar tanto si el usuario inicia sesión como si no"
   - Marca: "Ejecutar con los privilegios más altos"

4. **Pestaña Desencadenadores:**
   - Click en "Nuevo..."
   - En "Comienza la tarea": Selecciona "Al iniciar"
   - Marca: "Habilitado"
   - Click OK

5. **Pestaña Acciones:**
   - Click en "Nuevo..."
   - Acción: "Iniciar un programa"
   - Programa/script: 
     ```
     C:\xampp\htdocs\pecosol\python_api\AutoStart-Chatbot.vbs
     ```
   - Click OK

6. **Pestaña Condiciones:**
   - Deja los valores por defecto
   - Click OK

7. **Probar:**
   - Busca "Pecosol Chatbot" en el Programador de Tareas
   - Click derecho > "Ejecutar"
   - El servidor debería iniciar en segundo plano

---

## Opción 2: Atajo en Inicio de Windows

1. Presiona `Windows + R` y escribe:
   ```
   shell:startup
   ```

2. Copia `AutoStart-Chatbot.vbs` a esa carpeta
   
3. El chatbot se iniciará cada vez que abras Windows

---

## Opción 3: Acceso directo manual (Más rápido)

Si solo quieres ejecutarlo rápidamente antes de usar la aplicación:

1. Haz clic derecho en `AutoStart-Chatbot.vbs`
2. Selecciona "Crear acceso directo"
3. Coloca el acceso directo en tu Escritorio
4. Haz doble clic cuando necesites activar el chatbot

---

## Verificar que el servidor está corriendo:

Abre tu navegador en:
```
http://127.0.0.1:8000
```

Si ves un JSON con información del servidor, el chatbot está activo.

---

## Detener el servidor:

Si necesitas detener manualmente el servidor:

1. Abre PowerShell
2. Ejecuta:
   ```powershell
   Get-Process python | Stop-Process -Force
   ```

---

## Solucionar problemas:

**P: El chatbot sigue mostrando "Servidor Python no disponible"**

R: El servidor tarda ~3 segundos en iniciar. Espera unos segundos y recarga la página (F5).

**P: El servidor consume mucho recurso**

R: Esto es normal la primera vez. Si persiste, verifica los logs en la consola de Python.

**P: ¿Cada cuánto se reinicia el servidor?**

R: No se reinicia automáticamente. Corre continuamente una vez iniciado.
