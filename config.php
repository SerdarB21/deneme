<?php
return [
    'app_name' => 'Bayilik Sistemi',
    'db_path' => __DIR__ . '/database/database.sqlite',
    'upload_path' => __DIR__ . '/uploads',
    'allowed_image_types' => ['image/jpeg', 'image/png', 'image/gif'],
    'max_upload_size' => 5 * 1024 * 1024,
    'telegram_bot_token' => getenv('TELEGRAM_BOT_TOKEN') ?: '',
    'telegram_admin_chat_id' => getenv('TELEGRAM_ADMIN_CHAT_ID') ?: '',
];
