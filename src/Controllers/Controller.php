<?php declare(strict_types=1);

namespace Conflabs\JsonFileViewer\Controllers;


class Controller
{

    public function renderView(string $templateName, array $params): string
    {

        $loader = new \Twig\Loader\FilesystemLoader(constant('ROOT_PATH') . '/src/Views');
        $twig = new \Twig\Environment($loader, ['debug' => constant('VIEW_DEBUG'),]);
        $template = $twig->load($templateName);

        return $template->render($params);
    }
}