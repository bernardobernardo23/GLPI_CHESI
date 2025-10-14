<?php
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0777, true);
