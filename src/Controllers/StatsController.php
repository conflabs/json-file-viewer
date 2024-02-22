<?php

declare(strict_types=1);

namespace Conflabs\JsonFileViewer\Controllers;


use Conflabs\JsonFileViewer\Stats;
use Symfony\Component\HttpFoundation\Response;

class StatsController extends Controller
{

    private function links(): array
    {

        $filePath = constant('LOG_PATH') . '/' . 'stats.json';

        try {
            $contents = file_get_contents($filePath);
        } catch (\ErrorException $e) {
            $this->log->error("Error reading stats log file: $filePath");
            $this->log->error($e->getMessage());
            $this->log->error($e->getTraceAsString());
            die();
        }

        try {
            $contents = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->log->error("Error decoding stats log file: $filePath");
            $this->log->error($e->getMessage());
            $this->log->error($e->getTraceAsString());
            die();
        }
    }
    public function index(): void
    {

        $stats = new Stats();
        $links = $stats->links();
        unset($stats);


        $response = new Response($this->renderView('Stats.twig', [
            'year' => date('Y'),
            'links' => $links,
            'totalLinks' => array_sum(array_values($links)),
            'totalFiles' => count(array_values(array_filter(array_map(function ($file) {
                if (str_ends_with($file, 'json')) {
                    return 1;
                }
                return null;
            }, scandir(constant('CACHE_PATH')))))),
        ]), Response::HTTP_OK, ['content-type' => 'text/html']);
        $response->send();
    }
}