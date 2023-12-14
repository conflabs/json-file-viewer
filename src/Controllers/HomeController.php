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

        // If it does exist...
        if ($url) {

            // ...return the url parameter method.
            return $this->urlParameter($url);
        }

        // Get the id from the request, if it exists.
        $id = $_GET['id'] ?? null;

        // If it does exist...
        if ($id) {

            // ...return the id parameter method.
            return $this->idParameter($id);
        }

        // If the url and id aren't in the request, return the no parameter method.
        return $this->noParameter();
    }

    /**
     * @return Response
     */
    protected function noParameter(): Response
    {

        // ...return the default home page.
        $response = new Response($this->renderView('Home.twig', ['year' => date('Y')]), Response::HTTP_OK, ['content-type' => 'text/html']);
        return $response->send();
    }

    /**
     * @param string|null $url
     * @return Response
     */
    protected function urlParameter(string $url = null): Response
    {

        // If there's no URL in the request...
        if (!$url) {

            // ...Form a 400 Bad Request error with useful message...
            $response = new Response(json_encode([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'No URL found in request.'
            ]), Response::HTTP_BAD_REQUEST, ['content-type' => 'application/json']);

            // ...and return it.
            return $response->send();

            // The rest of the code in this method will only run if there is a URL in the request.
        }

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

        // Lint the json object for empty values, and replace them with a zero.
        // Quite specifically, this is a temporary fix to deal with empty value sets in the data.
        $buffer = preg_replace('/"value":\s+}/', '"value": 0}', $buffer);
        // Convert null strings to null values;
//        $buffer = str_replace('"null"', 'null', $buffer);

        // Return the buffer as a JSON response.
        $response = new Response($buffer, Response::HTTP_OK, ['content-type' => 'application/json']);

        return $response->send();
    }

    /**
     * @param string|null $id
     * @return Response
     */
    protected function idParameter(string $id = null): Response
    {

        // If there's no ID in the request...
        if (!$id) {

            // ...Form a 400 Bad Request error with useful message...
            $response = new Response(json_encode([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'No ID found in request.'
            ]), Response::HTTP_BAD_REQUEST, ['content-type' => 'application/json']);

            // ...and return it.
            return $response->send();

            // The rest of the code in this method will only run if there is an ID in the request.
        }

        // Get the file contents from Google Drive link and store in buffer.
        $buffer = file_get_contents('https://drive.google.com/uc?export=download&id=' . $id);

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

        // Convert null strings to null values;
//        $buffer = str_replace('"null"', 'null', $buffer);

        // Return the buffer as a JSON response.
        $response = new Response($buffer, Response::HTTP_OK, ['content-type' => 'application/json']);
        return $response->send();
    }

    public function cultivera(string $id = null): Response
    {
        // If there's no ID in the request...
        if (!$id) {

            // ...Form a 400 Bad Request error with useful message...
            $response = new Response(json_encode([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'No ID found in request.'
            ]), Response::HTTP_BAD_REQUEST, ['content-type' => 'application/json']);

            // ...and return it.
            return $response->send();

            // The rest of the code in this method will only run if there is an ID in the request.
        }

        // Get the file contents from Google Drive link and store in buffer.
        $buffer = file_get_contents('https://drive.google.com/uc?export=download&id=' . $id);

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

        // Convert null strings to null values;
        $buffer = str_replace('"null"', 'null', $buffer);

        // Return the buffer as a JSON response.
        $response = new Response($buffer, Response::HTTP_OK, ['content-type' => 'application/json']);
        return $response->send();
    }
}