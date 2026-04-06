<?php
// Aktifkan session
session_start();

// Timeout dalam detik (5 menit = 300 detik)
$timeout = 300;

if (isset($_SESSION['user'])) {
    if (isset($_SESSION['last_activity'])) {
        $inactive = time() - $_SESSION['last_activity'];
        if ($inactive > $timeout) {
            session_unset();
            session_destroy();
            header("Location: index.php?url=auth/login&timeout=1");
            exit;
        }
    }
    $_SESSION['last_activity'] = time();
}

// Tampilkan error untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$config = require_once __DIR__ . '/../app/config/config.php';
define('BASE_URL', $config['base_url']);

// Autoload class dari folder app
spl_autoload_register(function ($class) {
    $paths = [
        '../app/core/',
        '../app/controllers/',
        '../app/models/'
    ];

    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Tangkap URL dan pecah
$url = $_GET['url'] ?? 'auth/login';
$url = explode('/', filter_var($url, FILTER_SANITIZE_URL));

// Validasi controller dan method
$controllerName = !empty($url[0])  
    ? str_replace(' ', '', ucwords(str_replace(['-','_'],' ', $url[0]))) . 'Controller'  
    : 'AuthController';

if (!empty($url[1])) {
    $methodName = $url[1];
} else {
    $methodName = ($controllerName === 'AuthController') ? 'login' : 'index';
}
$params = array_slice($url, 2);

// Cek dan panggil controller
if (file_exists("../app/controllers/$controllerName.php")) {
    $controller = new $controllerName;

    if (method_exists($controller, $methodName)) {
        call_user_func_array([$controller, $methodName], $params);
    } else {
        echo "Method '$methodName' tidak ditemukan di controller '$controllerName'.";
    }
} else {
    echo "Controller '$controllerName' tidak ditemukan.";
}