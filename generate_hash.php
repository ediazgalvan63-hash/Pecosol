<?php
// generate_hash.php

// Ajusta la contraseña que deseas hashear:
$plainPassword = 'admin123';

// Generar el hash:
$hash = password_hash($plainPassword, PASSWORD_BCRYPT);

echo "Hash generado para '{$plainPassword}':\n";
echo $hash;
