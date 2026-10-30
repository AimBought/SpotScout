<?php

declare(strict_types=1);

const DB_USERNAME = getenv('POSTGRES_USER') ?: 'wdpai_user';
const DB_PASSWORD = getenv('POSTGRES_PASSWORD') ?: 'wdpai_password';
const DB_HOST = 'db';
const DB_PORT = 5432;
const DB_NAME = getenv('POSTGRES_DB') ?: 'wdpai_db';
