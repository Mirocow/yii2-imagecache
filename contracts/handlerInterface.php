<?php

namespace mirocow\imagecache\contracts;

interface handlerInterface
{
    public function runHandler(string $srcPath, string $targetFile);
}