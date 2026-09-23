<?php
/**
 * Redirect para o link de acesso configurado
 */

require_once 'config.php';

// Redirecionar para o link de acesso
header('Location: ' . ACCESS_LINK);
exit;