<?php

/**
 * @copyright (c) sota1235<sota1235@gmail.com>
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Views\Twig;

require 'vendor/autoload.php';

// Load Environment values
$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

/** Constants */
const LOGIN_SESSINO_KEY = 'LOGIN_SESSINO_KEY';

/** @var Array - Login user list. */
$LOGIN_USERS = [
    'subaru'     => '1amNeet!',
    'emilia'     => '0tank07su',
    'rem'        => 'morn1ngStar',
    'ram'        => 'fuka4!mo',
    'petelgeuse' => 'ta1da-death',
    'admin'      => getenv('DARK_CTF_Q3_ADMIN_PASS'),
];

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

/** Middlewares */

/** @var Closure */
$authMiddleware = function ($request, $response, $next) use ($container) {
    $session = $container['session'];

    $userInfo = $session->get(LOGIN_SESSINO_KEY);

    // Redirect to login page.
    if (is_null($userInfo)) {
        return $response->withRedirect(
            $request->getUri()->withPath(
                $this->router->pathFor('login.get')
            ), 302
        );
    }

    return $next($request, $response);
};

/** Routing */
$app->get('/login', function (Request $request, Response $response) {
    return $this->view->render($response, 'login.html.twig');
})->setName('login.get');

$app->post('/login', function (Request $request, Response $response) {
    $parsedBody = $request->getParsedBody();

    $id       = $parsedBody['id'];
    $password = $parsedBody['password'];
    global $LOGIN_USERS;

    if (!isset($LOGIN_USERS[$id])) {
        return $this->view->render($response, 'login.html.twig', [
            'errorMessage' => 'User '.$id.' not found.',
        ]);
    }

    if ($LOGIN_USERS[$id] !== $password) {
        return $this->view->render($response, 'login.html.twig', [
            'errorMessage' => 'Wrong password.',
        ]);
    }

    $this->session->set(LOGIN_SESSINO_KEY, [
        'login' => true,
        'admin' => ($id === 'admin'),
    ]);

    return $response->withRedirect(
        $request->getUri()->withPath(
            $this->router->pathFor('main.get')
        ), 302
    );
})->setName('login.post');

$app->post('/logout', function (Request $request, Response $response) {
    $this->session->set(LOGIN_SESSINO_KEY, null);

    return $response->withRedirect(
        $request->getUri()->withPath(
            $this->router->pathFor('login.get')
        ), 302
    );
})->setName('logout.post');

$app->get('/', function (Request $request, Response $response) {
    $userSession = $this->session->get(LOGIN_SESSINO_KEY);

    return $this->view->render($response, 'index.html.twig', [
        'isAdmin' => $userSession['admin'],
        'flag'    => getenv('DARK_CTF_Q3_FLAG'),
    ]);
})->setName('main.get')->add($authMiddleware);

/** Run app */
$app->run();
