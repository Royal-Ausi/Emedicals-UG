<?php
require_once __DIR__ . '/env.php';

define('SMTP_HOST', env_value('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', (int) env_value('SMTP_PORT', '587'));
define('SMTP_SECURE', env_value('SMTP_SECURE', 'tls'));

define('SMTP_USERNAME', env_value('SMTP_USERNAME', ''));
define('SMTP_PASSWORD', env_value('SMTP_PASSWORD', ''));

define('EMAIL_FROM_ADDRESS', env_value('EMAIL_FROM_ADDRESS', 'noreply@emedicals.ug'));
define('EMAIL_FROM_NAME', env_value('EMAIL_FROM_NAME', 'E-Medicals UG'));
