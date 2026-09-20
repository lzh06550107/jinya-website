<?php

$config = require dirname(__DIR__) . '/application/extra/upload.php';

if (empty($config['chunking'])) {
    fwrite(STDERR, "CMS upload chunking must be enabled.\n");
    exit(1);
}

if (!isset($config['chunksize']) || (int)$config['chunksize'] !== 1048576) {
    fwrite(STDERR, "CMS upload chunksize must be exactly 1048576 bytes.\n");
    exit(1);
}

if (!isset($config['maxsize']) || strtolower((string)$config['maxsize']) !== '10mb') {
    fwrite(STDERR, "The existing 10 MB total upload limit must be preserved.\n");
    exit(1);
}

fwrite(STDOUT, "cms_upload_config_test: PASS\n");
