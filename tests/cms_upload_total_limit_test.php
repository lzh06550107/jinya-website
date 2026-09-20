<?php

define('APP_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR);
define('ROOT_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('RUNTIME_PATH', ROOT_PATH . 'runtime' . DIRECTORY_SEPARATOR);

function __($text)
{
    $arguments = func_get_args();
    array_shift($arguments);
    return $arguments ? vsprintf($text, $arguments) : $text;
}

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
require ROOT_PATH . 'thinkphp/base.php';
set_error_handler(static function () {
    return true;
}, E_DEPRECATED | E_USER_DEPRECATED);

\think\Loader::addNamespace('app', APP_PATH);
\think\Config::set('upload', [
    'maxsize' => '1kb',
    'mimetype' => 'txt',
    'savekey' => '/uploads/{filemd5}{.suffix}',
]);

$chunkDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cms-upload-limit-' . bin2hex(random_bytes(8));
$chunkId = '12345678-1234-1234-1234-123456789012';
mkdir($chunkDir, 0755, true);
file_put_contents($chunkDir . DIRECTORY_SEPARATOR . $chunkId . '-0.part', str_repeat('a', 2048));

try {
    $upload = new \app\common\library\Upload();
    $upload->setChunkDir($chunkDir);
    $upload->merge($chunkId, 1, 'oversized.txt');
    fwrite(STDERR, "Merged uploads larger than maxsize must be rejected.\n");
    exit(1);
} catch (\app\common\exception\UploadException $exception) {
    if (strpos($exception->getMessage(), 'File is too big') === false) {
        fwrite(STDERR, "Expected a total-size error, got: {$exception->getMessage()}\n");
        exit(1);
    }
} catch (\Throwable $exception) {
    fwrite(STDERR, "Expected a total-size error, got " . get_class($exception) . ": {$exception->getMessage()}\n");
    exit(1);
} finally {
    foreach (glob($chunkDir . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
        @unlink($file);
    }
    @rmdir($chunkDir);
}

fwrite(STDOUT, "cms_upload_total_limit_test: PASS\n");
