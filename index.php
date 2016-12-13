<?php

/**
 * @copyright (c) sota1235<sota1235@gmail.com>
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Views\Twig;

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

$container['session'] = function ($container) {
    return new \SlimSession\Helper;
};

/** Routing */
$app->get('/login', function (Request $request, Response $response) {
    return $this->view->render($response, 'login.html.twig');
})->setName('login');

/** Run app */
$app->run();
