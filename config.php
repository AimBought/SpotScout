<?php

declare(strict_types=1);

define('DB_USERNAME', getenv('POSTGRES_USER') ?: 'wdpai_user');
define('DB_PASSWORD', getenv('POSTGRES_PASSWORD') ?: 'wdpai_password');
define('DB_HOST', 'db');
define('DB_PORT', 5432);
define('DB_NAME', getenv('POSTGRES_DB') ?: 'wdpai_db');
