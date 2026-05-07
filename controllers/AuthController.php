<?php
// controllers/AuthController.php

class AuthController {
    private $userModel;

    public function __construct() {
        // 1) Asegurar que la sesión esté iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // 2) Incluir el modelo de usuario
        require_once __DIR__ . '/../models/User.php';
        $this->userModel = new User();
    }

    /**
     * login()
     * - Si es GET: muestra el formulario de login.
     * - Si es POST: procesa las credenciales y redirige según rol.
     */
    public function login() {
        // Si viene de un POST, procesar credenciales
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // a) Obtener datos enviados
            $username = trim($_POST['username'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Agregar logging para debug
            error_log("=== LOGIN ATTEMPT ===");
            error_log("Username: '" . $username . "' (length: " . strlen($username) . ")");
            error_log("Password length: " . strlen($password));

            // b) Validar que ambos campos no estén vacíos
            $error = '';
            if ($username === '' || $password === '') {
                $error = 'Usuario y contraseña son obligatorios.';
                error_log("ERROR: Empty username or password");
                require __DIR__ . '/../views/auth/login.php';
                return;
            }

            // c) Buscar usuario en BD por username
            $user = $this->userModel->findByUsername($username);
            if ($user) {
                error_log("User found: " . $user->username . " (role: " . $user->role . ")");
                
                // d) Verificar la contraseña
                // Si BYPASS_PASSWORD_VERIFICATION está definido (troubleshooting), acepta cualquier contraseña
                $passwordVerified = (defined('BYPASS_PASSWORD_VERIFICATION') && BYPASS_PASSWORD_VERIFICATION) 
                    ? true 
                    : $this->userModel->verifyPassword($password, $user->password);
                
                error_log("Password verified: " . var_export($passwordVerified, true));
                if (defined('BYPASS_PASSWORD_VERIFICATION') && BYPASS_PASSWORD_VERIFICATION) {
                    error_log("WARNING: Password verification bypassed for troubleshooting!");
                }
                
                if ($passwordVerified) {
                    // Credenciales correctas: guardar datos en sesión
                    $_SESSION['user_id']   = $user->id;
                    $_SESSION['username']  = $user->username;
                    $_SESSION['full_name'] = $user->full_name;
                    $_SESSION['role']      = $user->role;
                    
                    error_log("Login successful! Session established. Redirecting to role home.");

                    // Redirigir al panel según rol
                    header('Location: index.php?controller=dashboard&action=home');
                    exit;
                } else {
                    // Contraseña incorrecta
                    $error = 'Contraseña incorrecta.';
                    error_log("ERROR: Password verification failed");
                    require __DIR__ . '/../views/auth/login.php';
                    return;
                }
            } else {
                // El usuario no existe
                $error = 'Usuario no existe.';
                error_log("ERROR: User not found");
                require __DIR__ . '/../views/auth/login.php';
                return;
            }
        }

        // Si no es POST, simplemente mostrar el formulario de login
        require __DIR__ . '/../views/auth/login.php';
    }

    /**
     * logout()
     * - Destruye la sesión y redirige al formulario de login.
     */
    public function logout() {
        // 1) Si la sesión está activa, eliminar todas las variables y destruirla
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_unset();
            session_destroy();
        }
        // 2) Volver al login
        header('Location: index.php?controller=auth&action=login');
        exit;
    }
}
