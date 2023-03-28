# GeekMusclay Router

## Setup
Simply clone this pckage and run `composer install` command.

> **/!\ WARNING** This package require `geekmusclay/di-container` currently under development
> To use this package you will have to donwload it, adn require it locally

## Getting started

```php
declare(strict_types=1);

require '../vendor/autoload.php';

use Geekmusclay\DI\Core\Container;
use Geekmusclay\Router\Core\Router;
use GuzzleHttp\Psr7\ServerRequest;

$container = new Container();
$router = new Router($container);

$router->get('/', function () {
    echo 'Hello World !';
});

try {
    $router->run(ServerRequest::fromGlobals());
} catch (Exception $e) {
    dd($e->getMessage());
}
```

## Routing

```php
$router->get('/', function () {
    echo 'GET route';
});

$router->post('/', function () {
    echo 'POST route';
});

$router->put('/put', function () {
    echo 'PUT route';
});

$router->delete('/delete', function () {
    echo 'DELETE route';
});
```

## Group routes

```php
$router->group('/api/v1', function (RouterInterface $group) use ($router) {

    $group->get('/', function () {
        echo 'Welcome on api !';
    }, 'api.v1.index');

    $group->get('/coucou', function () {
        echo 'Coucou';
    }, 'api.v1.coucou');

    $group->get('/:id', function (int $id) {
        echo 'Coucou n°' . $id;
    }, 'api.v1.coucou.detail')->with([
        'id' => '[0-9]+',
    ]);

    $group->group('/sub', function (RouterInterface $subgroup) use ($router) {

        $subgroup->get('/', function () use ($router) {
            echo 'Sub index : ' . $router->path('api.v1.sub.index');
        }, 'api.v1.sub.index');

        $subgroup->get('/test', function () {
            echo 'Sub test';
        }, 'api.v1.sub.test');

        $subgroup->get('/:id', function (int $id) {
            echo 'Sub n°' . $id;
        }, 'api.v1.sub.detail')->with([
            'id' => '[0-9]+',
        ]);

    });

});
```

## Using PHP 8 attributes

```php
$router->register(MyController::class);
```

```php
use Geekmusclay\Router\Attribute\Route;
use Psr\Http\Message\ServerRequestInterface as Request;

#[Route(path: '/prefixed')]
class MyController
{
    #[Route(path: '/', name: 'fake.index')]
    public function index()
    {
        return 'Index';
    }

    #[Route(path: '/hello', name: 'fake.hello')]
    public function hello()
    {
        return 'Hello';
    }

    #[Route(path: '/static', name: 'fake.static')]
    public static function staticHello()
    {
        return 'Hello';
    }

    #[Route(path: '/:id-:slug', name: 'fake.complex', with: [
        'id' => '[0-9]+',
        'slug' => '[a-z\-]+'
    ])]
    public function complex(Request $request, int $id, string $slug)
    {
        return 'Method: ' . $request->getMethod() . ', Id: ' . $id . ', Slug: ' . $slug;
    }
}
```

## License

This package is under MIT licence.

**Have fun!**