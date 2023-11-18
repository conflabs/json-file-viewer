<?php declare(strict_types=1);

namespace Conflabs\JsonFileViewer\Controllers;



use Symfony\Component\HttpFoundation\Response;

final class HomeController extends Controller
{

    /**
     * INDEX: This method is the primary entry point for the application; a sole route.
     *
     * @return Response
     */
    public function index(): Response
    {
        // Get the url link from the request, if it exists.
        $url = $_GET['url'] ?? null;

        // If the url link isn't in the request...
        if (!$url) {

            // ...return the default home page.
            $response = new Response($this->renderView('Home.twig', ['year' => date('Y')]), Response::HTTP_OK, ['content-type' => 'text/html']);

            return $response->send();

            // Otherwise, if the url link is in the request...
        } else {

            // Check to see that it's a Google Drive URL.
            $urlIsGoogleDrive = str_contains($url, 'https://drive.google.com/file/d/');

            // If it's not a Google Drive URL...
            if (!$urlIsGoogleDrive) {

                // ...return a 400 Bad Request error with useful message.
                $response = new Response(json_encode([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Invalid URL in request. Must be a Google Drive URL.',
                ]), Response::HTTP_BAD_REQUEST, ['content-type' => 'application/json']);

                return $response->send();

                // The rest of the code in this method will only run if the URL is a Google Drive URL.
            }

            // Remove everything but the file ID from the URL.
            $fileId = preg_replace('/\/view.*/', '', str_replace('https://drive.google.com/file/d/', '', $url));

            // Get the file contents from Google Drive link and store in buffer.
            $buffer = file_get_contents('https://drive.google.com/uc?export=download&id=' . $fileId);

            // If the buffer is empty...
            if (!$buffer) {

                // ...return a 400 Bad Request error with useful message.
                $response = new Response(json_encode([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'No data found for this URL.'
                ]), Response::HTTP_BAD_REQUEST, ['content-type' => 'application/json']);

                return $response->send();

                // The rest of the code in this method will only run if the buffer is not empty.
            }

            // Return the buffer as a JSON response.
            $response = new Response($buffer, Response::HTTP_OK, ['content-type' => 'application/json']);
            return $response->send();
        }
    }
}