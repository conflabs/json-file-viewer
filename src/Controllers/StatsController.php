<?php

declare(strict_types=1);

namespace Conflabs\JsonFileViewer\Controllers;


use Symfony\Component\HttpFoundation\Response;

class StatsController extends Controller
{

    public function index(): void
    {

        $filePath = constant('LOG_PATH') . '/' . 'stats.json';

        $contents = json_decode(file_get_contents($filePath), true);

        $links = [];
        foreach ($contents as $key => $value) {
            $links[$key] = $value;
        }

        // Sort the array by values, DESC
        uasort($links, function (mixed $a, mixed $b): int
        {
            if ($a == $b) {
                return 0;
            }

            return ($a > $b) ? -1 : 1;
        });

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