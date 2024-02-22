<?php

declare(strict_types=1);

namespace Conflabs\JsonFileViewer;


use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;

final class AppLog
{
    public Logger $log;

    public function __construct(array $logConfig = [])
    {
        if (!isset($logConfig['log_name'])) {
            $logConfig = [
                'log_name' => constant('LOG_NAME'),
                'log_path' => constant('LOG_PATH') . '/' . constant('LOG_NAME').'_'.date('Y-m-d').'.log',
                'log_level' => constant('VIEW_DEBUG')
                    ? 'debug'
                    : 'warning',
            ];
        }

        $this->log = new Logger($logConfig['log_name']);
        $this->log->pushHandler(new StreamHandler($logConfig['log_path'], $logConfig['log_level']
            ? Level::Debug
            : Level::Warning));
    }
}