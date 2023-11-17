<?php declare(strict_types=1);

namespace Conflabs\JsonFileViewer\Controllers;


final class HomeController extends Controller
{


    public function index(): void
    {

        $fileName = $_GET['fileName'] ?? null;

        if (!$fileName) {

            echo $this->renderView('Home.twig', []);
        } else {

            header('Content-Type: application/json');
            echo file_get_contents(constant('JSON_STORAGE_PATH') . '/' . $fileName);
        }
    }
}