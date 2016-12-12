<?php

/**
 * @copyright (c) sota1235<sota1235@gmail.com>
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\View\Twig;

require 'vendor/autoload.php';

$app = new \Slim\App;
$container = $app->getContainer();

/** Container Config */
$container['view'] = function ($container) {
    $view = new Twig('views', [
        'cache' => 'storage/view',
    ]);

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

    return $view;
};

/** Routing */
$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $response->getBody()->write("Hello, $name");

    return $response;
});

/** Run app */
$app->run();
