<?php
// check_openai.php - Herramienta rápida para verificar qué ve Apache/PHP respecto a la clave OPENAI_API_KEY
header('Content-Type: text/plain; charset=utf-8');

echo "Comprobación de OPENAI_API_KEY para el proceso PHP que sirve esta petición:\n\n";

$env_get = getenv('OPENAI_API_KEY');
$env_superglobal = isset($_ENV['OPENAI_API_KEY']) ? $_ENV['OPENAI_API_KEY'] : '<no set in $_ENV>';

echo "getenv('OPENAI_API_KEY') => ";
echo ($env_get === false || $env_get === '') ? "<vacía>" : $env_get;

// check_openai.php - Herramienta rápida para verificar qué ve Apache/PHP respecto a la clave OPENAI_API_KEY
header('Content-Type: text/plain; charset=utf-8');

echo "Comprobación de OPENAI_API_KEY para el proceso PHP que sirve esta petición:\n\n";

$env_get = getenv('OPENAI_API_KEY');
$env_superglobal = isset($_ENV['OPENAI_API_KEY']) ? $_ENV['OPENAI_API_KEY'] : '<no set in _ENV>';

echo "getenv('OPENAI_API_KEY') => ";
echo ($env_get === false || $env_get === '') ? "<vacía>" : $env_get;
echo "\n";

echo "\$_ENV['OPENAI_API_KEY'] => ";
echo ($env_superglobal === '') ? "<vacía>" : $env_superglobal;
echo "\n\n";

// Información adicional útil
echo "PHP SAPI: " . php_sapi_name() . "\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "Ruta base del proyecto: " . __DIR__ . "\n";

// Sugiere pasos si está vacía
if (empty($env_get)) {
    echo "\nADVERTENCIA: OPENAI_API_KEY no está definida para el proceso PHP.\n";
    echo "Opciones: 1) Definir variable de entorno Machine e reiniciar Apache (recomendado).\n";
    echo "         2) Añadir SetEnv en httpd.conf/vhost y reiniciar Apache.\n";
    echo "         3) (temporal) definir la clave en config/openai.php, pero NO hacerlo en repositorio.\n";
}

?>