<?php

declare(strict_types=1);

namespace Conflabs\JsonFileViewer;

use Conflabs\JsonFileViewer\Controllers\ErrorController;
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
        $this->filePath = constant('LOG_PATH') . '/stats.json';
    }

    /**
     * @return void
     */
    public function verifyStatsFile(): void
    {
        if (!file_exists($this->filePath)) {
            try {
                // Create the file if it doesn't exist
                file_put_contents($this->filePath, json_encode([]));
            } catch (Exception $e) {
                // Log the error if the create attempt fails, then die silent.
                $this->log->error("Error creating stats log file: $this->filePath");
                $this->log->error($e->getMessage());
                $this->log->error($e->getTraceAsString());

                if (constant('VIEW_DEBUG')) {
                    (new ErrorController())->internalServerError([
                        "Error creating stats log file: $this->filePath",
                        $e->getMessage(),
                        $e->getTraceAsString(),
                    ]);
                }

                (new ErrorController())->internalServerError();
            }
        }
    }

    /**
     * @return false|string
     */
    public function getStatsFileContents(): false|string
    {

        try {
            // Try to read the file contents
            $contents = file_get_contents($this->filePath);
        } catch (Exception $e) {
            // Log the error if the read attempt fails, then die silent.
            $this->log->error("Error reading stats log file: $this->filePath");
            $this->log->error($e->getMessage());
            $this->log->error($e->getTraceAsString());

            if (constant('VIEW_DEBUG')) {
                (new ErrorController())->internalServerError([
                    "Error reading stats log file: $this->filePath",
                    $e->getMessage(),
                    $e->getTraceAsString(),
                ]);
            }

            (new ErrorController())->internalServerError();
        }

        return $contents;
    }

    /**
     * @param string $contents
     * @return array|string
     */
    public function decodeStatsFileContents(string $contents): array|String
    {

        try {
            // Try to decode the file contents
            $contents = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            // Log the error if the decode attempt fails, then die silent.
            $this->log->error("Error decoding stats log file: $this->filePath");
            $this->log->error($e->getMessage());
            $this->log->error($e->getTraceAsString());

            if (constant('VIEW_DEBUG')) {

                (new ErrorController())->internalServerError([
                    "Error decoding stats log file: $this->filePath",
                    $e->getMessage(),
                    $e->getTraceAsString(),
                ]);
            }

            (new ErrorController())->internalServerError();
        }

        // Return the contents
        return $contents;
    }

    /**
     * @param array $contents
     * @return void
     */
    public function commitStatsFile(array $contents): void
    {

        try {
            // Try to write the file contents
            file_put_contents($this->filePath, json_encode($contents));
        } catch (Exception $e) {
            // Log the error if the write attempt fails
            $this->log->error("Error writing stats log file: $this->filePath");
            $this->log->error($e->getMessage());
            $this->log->error($e->getTraceAsString());

            if (constant('VIEW_DEBUG')) {
                // Return the errors if in dev
                (new ErrorController())->internalServerError([
                    "Error writing stats log file: $this->filePath",
                    $e->getMessage(),
                    $e->getTraceAsString()
                ]);
            }

            // Return 500 in prod
            (new ErrorController())->internalServerError();
        }
    }

    /**
     * @param $fileId
     * @return bool
     */
    public function clicked($fileId): bool
    {

        // Verify the stats file exists
        $this->verifyStatsFile();
        
        // retrieve the file json contents
        $contents = $this->getStatsFileContents();
        
        // decode the file json contents
        $contents = $this->decodeStatsFileContents($contents);
        
        // Add the new click to the stats
        $contents[$fileId][$_SERVER['REMOTE_ADDR']][] = date("Y-m-d H:i:s");
        
        // Commit the new stats to the file
        $this->commitStatsFile($contents);

        return true;
    }

    public function links(): array
    {
        // Verify the stats file exists
        $this->verifyStatsFile();

        // retrieve the file json contents
        $contents = $this->getStatsFileContents();

        // decode the file json contents
        $contents = $this->decodeStatsFileContents($contents);

        // Get the group ids
        $gids = array_keys($contents);

        $counts = [];
        foreach ($gids as $gid) {
            $count = 0;
            foreach ($contents[$gid] as $ip => $clicks) {
                $count += count($clicks);
            }
            $counts[$gid] = $count;
        }

        // Get the counts of each group
        $counts = array_map(function ($gid) use ($contents) {
            $count = 0;
            foreach ($contents[$gid] as $ip => $clicks) {
                $count += count($clicks);
            }

            return $count;
        }, $gids);

        // Combine the group ids and counts into an array
        $links = array_combine($gids, $counts);

        // Sort the array by values, DESC
        uasort($links, function (mixed $a, mixed $b): int
        {
            if ($a == $b) {
                return 0;
            }

            return ($a > $b) ? -1 : 1;
        });

        // Return the sorted array
        return $links;
    }
}