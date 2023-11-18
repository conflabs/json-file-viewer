<?php declare(strict_types=1);

namespace Conflabs\JsonFileViewer\Controllers;


final class HomeController extends Controller
{

    public function index(): void
    {
        $url = $_GET['url'] ?? null;

        if (!$url) {

            echo $this->renderView('Home.twig', []);
        } else {

            $urlIsGoogleDrive = str_contains($url, 'https://drive.google.com/file/d/');
            if (!$urlIsGoogleDrive) {

                header("HTTP/1.1 400 OK");
                header('Content-Type: application/json');;
                echo json_encode([
                    'status' => 400,
                    'message' => 'Invalid URL in request. Must be a Google Drive URL.',
                ]);
                return;
            }

            $fileId = preg_replace('/\/view.*/', '', str_replace('https://drive.google.com/file/d/', '', $url));

            $buffer = file_get_contents('https://drive.google.com/uc?export=download&id=' . $fileId);
            if (!$buffer) {
                header("HTTP/1.1 400 OK");
                header('Content-Type: application/json');;
                echo json_encode([
                    'status' => 400,
                    'message' => 'Content empty. No data found for this URL.',
                ]);
                return;
            }

            header("HTTP/1.1 200 OK");
            header('Content-Type: application/json');
            echo $buffer;
        }
    }
}