<?php
session_start();

if (isset($_SESSION['idUsuario'])) {
    header("Location: index.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/business/bUsuario.php';
    
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Por favor, complete todos los campos';
    } else {
        $usuarioBusiness = new bUsuario();
        $resultado = $usuarioBusiness->autenticar($username, $password);
        
        if ($resultado['success']) {
            header("Location: index.php");
            exit;
        } else {
            $error = $resultado['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Abastecimiento</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
    <div class="particles-container" id="particles"></div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo">
                    <i class="fas fa-building"></i>
                    <div class="logo-ring"></div>
                </div>
                <h1>Sistema de Abastecimiento</h1>
                <p>Municipalidad - Gestión de Oficina</p>
            </div>
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" id="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                    <button type="button" class="alert-close" onclick="closeAlert()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($success)): ?>
                <div class="alert alert-success" id="alert">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($success); ?></span>
                    <button type="button" class="alert-close" onclick="closeAlert()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="login-form" id="loginForm" novalidate>
                <div class="form-group">
                    <label for="username">
                        <i class="fas fa-user"></i> Usuario
                    </label>
                    <div class="input-wrapper">
                        <input type="text" 
                               id="username" 
                               name="username" 
                               placeholder="Ingrese su usuario"
                               autocomplete="username"
                               required 
                               autofocus>
                        <span class="input-icon"><i class="fas fa-user"></i></span>
                    </div>
                    <span class="error-message" id="usernameError"></span>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i> Contraseña
                    </label>
                    <div class="input-wrapper">
                        <input type="password" 
                               id="password" 
                               name="password" 
                               placeholder="Ingrese su contraseña"
                               autocomplete="current-password"
                               required>
                        <span class="input-icon"><i class="fas fa-lock"></i></span>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    <span class="error-message" id="passwordError"></span>
                </div>
                
                <div class="form-options">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" id="rememberMe" name="rememberMe">
                        <span class="checkbox-label">Recordarme</span>
                    </label>
                </div>
                
                <button type="submit" class="btn-login" id="btnLogin">
                    <span class="btn-text">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </span>
                    <span class="btn-loader" style="display: none;">
                        <i class="fas fa-circle-notch fa-spin"></i> Ingresando...
                    </span>
                </button>
            </form>
            
            <div class="login-footer">
                <p><i class="fas fa-shield-alt"></i> Sistema de Abastecimiento Municipal</p>
                <p class="version">v1.0.0</p>
            </div>
        </div>
        
    </div>

    <script src="assets/js/login.js"></script>
</body>
</html>