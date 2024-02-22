<?php declare(strict_types=1);

namespace Conflabs\JsonFileViewer\Controllers;


use Monolog\Logger;
use Symfony\Component\HttpFoundation\Response;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Controller
{

    protected Logger $log;

    public function __construct()
    {
        $this->log = $GLOBALS['appLog'];
    }

    public function renderView(string $templateName, array $params): string
    {

        $loader = new \Twig\Loader\FilesystemLoader(constant('ROOT_PATH') . '/src/Views');
        $twig = new \Twig\Environment($loader, ['debug' => constant('VIEW_DEBUG'),]);

        try {
            $template = $twig->load($templateName);
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            $this->log->error($e->getMessage());
            $this->log->error($e->getTraceAsString());

            if (constant('VIEW_DEBUG')) {
                $response = new Response(json_encode(['error' => $e->getMessage()]), Response::HTTP_INTERNAL_SERVER_ERROR, ['content-type' => 'application/json']);
                $response->send();
            } else {
                $response = new Response($this->renderView('404.twig', ['year' => date('Y')]), Response::HTTP_OK, ['content-type' => 'text/html']);
                $response->send();
            }

            die();
        }

        return $template->render($params);
    }
}