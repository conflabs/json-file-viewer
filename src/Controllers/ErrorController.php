<?php declare(strict_types=1);

namespace Conflabs\JsonFileViewer\Controllers;

use Conflabs\JsonFileViewer\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends Controller
{

    /**
     * ERROR:
     * A catch-all route for any request that doesn't match a route in the dispatcher.
     * Returns a custom 404 page.
     *
     * @return Response
     */
    public function index(): Response
    {

        $response = new Response($this->renderView('404.twig', ['year' => date('Y')]), Response::HTTP_OK, ['content-type' => 'text/html']);

        return $response->send();
    }

    public function internalServerError(array $errorMessages = []): Response
    {
        $params = [
            'year' => date('Y'),
        ];

        If ($errorMessages !== []) {
            $params['errorMessages'] = $errorMessages;
        }

        $response = new Response($this->renderView('500.twig', $params), Response::HTTP_INTERNAL_SERVER_ERROR, ['content-type' => 'text/html']);

        return $response->send();
    }
}