<?php declare(strict_types=1);

namespace Conflabs\JsonFileViewer\Controllers;

use \Exception;
use Conflabs\JsonFileViewer\Models\ParamTypes;
use Conflabs\JsonFileViewer\Stats;
use Conflabs\JsonFileViewer\Traits\GoogleDriveHelperTrait;
use Conflabs\JsonFileViewer\Traits\ValidationHelperTrait;
use Symfony\Component\HttpFoundation\Response;


final class HomeController extends Controller
{

    /**
     * Load the Helper Traits into this class.
     */
    use GoogleDriveHelperTrait, ValidationHelperTrait;

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

            return $this->run($url, ParamTypes::URL, false);

            // The rest of the code in this method will only run if there is no URL in the request.
        }

        // Get the id from the request, if it exists.
        $id = $_GET['id'] ?? null;

        // If it does exist...
        if ($id) {

            return $this->run($id, ParamTypes::ID, false);

            // The rest of the code in this method will only run if there is no ID in the request.
        }

        // Get the cultivera param from the request, if it exists.
        $cultivera = $_GET['cultivera'] ?? null;

        // If it does exist...
        if ($cultivera) {

            return $this->run($cultivera, ParamTypes::CULTIVERA, true);

            // The rest of the code in this method will only run if there is no CULTIVERA in the request.
        }

        // Get the alt param from the request, if it exists.
        $alt = $_GET['alt'] ?? null;

        // If it does exist...
        if ($alt) {

            return $this->run($alt, ParamTypes::ALT, true);

            // The rest of the code in this method will only run if there is no ALT in the request.
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
     * @param string|null $param
     * @param ParamTypes $paramType
     * @param bool $noStringNulls
     * @return Response
     */
    protected function run(string $param = null, ParamTypes $paramType = ParamTypes::ID, bool $noStringNulls = false): Response
    {

        // If there's no ID in the request...
        if (!$param) {

            // ...Form a 400 Bad Request error with useful message...
            $response = new Response(json_encode([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'No ID, ALT, CULTIVERA, or URL param found in request.'
            ]), Response::HTTP_BAD_REQUEST, ['content-type' => 'application/json']);

            // ...and return it.
            return $response->send();

            // The rest of the code in this method will only run if there is an ID in the request.
        }

        // If the param type is URL...
        if ($paramType == ParamTypes::URL) {
            // Check to see that it's a Google Drive URL.
            $urlIsGoogleDriveLink = self::isGoogleDriveLink($param);
            // If it is a Google Drive URL...
            if ($urlIsGoogleDriveLink) {
                // ...Set the Param to be a File ID.
                $param = self::getGoogleDriveFileId($param);
            // Else
            } else {
                // ...form a 400 Bad Request error with useful message...
                $response = new Response(json_encode([
                    'status' => Response::HTTP_BAD_REQUEST,
                    'message' => 'Invalid URL in request. Must be a Google Drive URL.',
                ]), Response::HTTP_BAD_REQUEST, ['content-type' => 'application/json']);

                // ...and return it.
                return $response->send();
            }
        }

        // Log the request to the stats file.
        $stats = new Stats();
        $stats->clicked($param);
        unset($stats);

        // Check to see if the file already exists in the cache.
        $googleDriveFileAlreadyInCache = file_exists(constant('CACHE_PATH') . '/' . $param . '.json');

        // If it does, get the file contents from the cache and store in buffer.
        if ($googleDriveFileAlreadyInCache) {
            // If it does, get the file contents from the cache and store in buffer.
            $buffer = file_get_contents(constant('CACHE_PATH') . '/' . $param . '.json');
            try {

                // Validate the JSON in the buffer.
                if (!json_validate($buffer)) {
                    throw new Exception('Invalid JSON in cache file.');
                };
            } catch (Exception $e) {
                // Log the error.
                $this->log->error($e->getMessage());
                $this->log->error($e->getTraceAsString());

                // If the environment is not production, echo the error and die.
                if (constant('VIEW_DEBUG')) {

                    (new ErrorController())->internalServerError([
                        'Invalid JSON in cache file.',
                        $e->getMessage(),
                        $e->getTraceAsString(),
                    ]);
                }

                // ...form a 500 Internal Server error with useful message...
                $response = new Response(json_encode([
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Invalid JSON in cache file. Please request support.',
                ]), Response::HTTP_BAD_REQUEST, ['content-type' => 'application/json']);

                // ...and return it.
                return $response->send();
            }
        } else {
            // If it doesn't, get the file contents from Google Drive link and store in buffer.
            $buffer = self::getGoogleDriveFileContentsByFileId($param);
            // Then write it to a file in the cache.
            $filePath = constant('CACHE_PATH') . "/$param.json";

            try {
                if ($buffer !== '') {
                    file_put_contents($filePath, $buffer);
                }
            } catch (Exception $e) {
                // Log the error.
                $this->log->error($e->getMessage());
                $this->log->error($e->getTraceAsString());

                if (constant('VIEW_DEBUG')) {
                    (new ErrorController())->internalServerError([
                        'Error writing to cache file.',
                        $e->getMessage(),
                        $e->getTraceAsString(),
                    ]);
                }

                // ...form a 500 Internal Server error with useful message...
                $response = new Response(json_encode([
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'message' => 'Error writing to cache file. Please request support.',
                ]), Response::HTTP_BAD_REQUEST, ['content-type' => 'application/json']);

                // ...and return it.
                return $response->send();
            }
        }

        // If the buffer is empty...
        if (!$buffer) {

            // ...return a 400 Bad Request error with useful message.
            $response = new Response(json_encode([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'No data found for this id: ' . $param,
            ]), Response::HTTP_BAD_REQUEST, ['content-type' => 'application/json']);

            return $response->send();

            // The rest of the code in this method will only run if the buffer is not empty.
        }

        // Convert null strings to null values;
        $buffer = $noStringNulls ? self::validateNullStringsToValues($buffer) : $buffer;

        // Get the product_name values from the buffer object that exceed a char length of 300.
        $productNames = self::validateProductNameLengths($buffer);

        // If there are any product_name values that exceed 300 characters...
        if ($productNames) {

            $this->log->error('One or more product_name values exceeds 300 characters.');

            // ...return a 400 Bad Request error with useful message.
            $response = new Response(json_encode([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'One or more product_name values exceeds 300 characters.' . PHP_EOL . json_encode($productNames),
            ]), Response::HTTP_BAD_REQUEST, ['content-type' => 'application/json']);

            return $response->send();

            // The rest of the code in this method will only run if the names are validated.
        }

        // Get the qty values from the buffer object that are equal to zero.
        $productQuantities = self::validateProductQuantities($buffer);

        // If there are any qty values that equal zero...
        if ($productQuantities) {

            // ...return a 400 Bad Request error with useful message.
            $response = new Response(json_encode([
                'status' => Response::HTTP_BAD_REQUEST,
                'message' => 'One or more qty values equals zero.' . PHP_EOL . json_encode($productQuantities),
            ]), Response::HTTP_BAD_REQUEST, ['content-type' => 'application/json']);

            return $response->send();

            // The rest of the code in this method will only run if the quantities are validated.
        }

        // Return the buffer as a JSON response.
        $response = new Response($buffer, Response::HTTP_OK, ['content-type' => 'application/json']);
        return $response->send();
    }
}