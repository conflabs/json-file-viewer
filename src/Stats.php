<?php

declare(strict_types=1);

namespace Conflabs\JsonFileViewer;

use Conflabs\JsonFileViewer\Traits\GoogleDriveHelperTrait;
use Exception;
use Monolog\Logger;

/**
 * Class Stats
 * @package Conflabs\JsonFileViewer
 *
 * A class that handles a json file counting the number of times a link is clicked
 */
final class Stats
{

    use GoogleDriveHelperTrait;

    private string $filePath;
    private string $fileId;

    private Logger $log;

    public function __construct()
    {

        $this->log = $GLOBALS['appLog'];

        $filePath = constant('LOG_PATH') . '/stats.json';
        if (!file_exists($filePath)) {
            file_put_contents($filePath, json_encode([]));
        }

        $this->filePath = $filePath;
    }

    public function clicked($fileId): bool
    {

        $contents = json_decode(file_get_contents($this->filePath), true);

        if (isset($contents[$fileId])) {
            $contents[$fileId]++;
        } else {
            $contents[$fileId] = 1;
        }

        try {
            file_put_contents($this->filePath, json_encode($contents));
        } catch (Exception $e) {
            $this->log->error($e->getMessage());
            $this->log->error($e->getTraceAsString());
            if (constant('APP_ENV') !== 'production') {
                echo $e->getMessage();
                echo $e->getTraceAsString();
                die();
            }
            echo $e->getMessage();
            echo $e->getTraceAsString();
        }

        return true;
    }
}