<?php

/**
 * @copyright (c) sota1235<sota1235@gmail.com>
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Views\Twig;

require 'vendor/autoload.php';

/** Constants */
const LOGIN_SESSINO_KEY = 'LOGIN_SESSINO_KEY';

/** Initialization */
$app = new \Slim\App;
$app->add(new \Slim\Middleware\Session([
    'name'        => 'dark_ctf_session',
    'autorefresh' => false,
    'lifetime'    => '1 hour',
]));
$container = $app->getContainer();

/** Container Config */
$container['view'] = function ($container) {
    $view = new Twig('views');

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
})->setName('login.get');

$app->post('/login', function (Request $request, Response $response) {
    $parsedBody = $request->getParsedBody();

    $id       = $parsedBody['id'];
    $password = $parsedBody['password'];

    // TODO: Authenticate

    $this->session->set(LOGIN_SESSINO_KEY, [
        'login' => true,
        'admin' => true,
    ]);

    return $response->withRedirect(
        $request->getUri()->withPath(
            $this->router->pathFor('main.get')
        ), 302
    );
})->setName('login.post');

$app->get('/', function (Request $request, Response $response) {
    return $this->view->render($response, 'index.html.twig');
})->setName('main.get');

/** Run app */
$app->run();
