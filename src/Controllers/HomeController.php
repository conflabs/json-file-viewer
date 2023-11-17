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

            $fileId = preg_replace('/\/view.*/', '', str_replace('https://drive.google.com/file/d/', '', $url));

            header('Content-Type: application/json');
            echo file_get_contents('https://drive.google.com/uc?export=download&id=' . $fileId);
        }
    }
}