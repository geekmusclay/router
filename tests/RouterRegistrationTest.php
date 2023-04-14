<?php

use PHPUnit\Framework\TestCase;
use Geekmusclay\DI\Core\Container;
use GuzzleHttp\Psr7\ServerRequest;
use Geekmusclay\Router\Core\Router;
use Tests\Fake\FakeAttributeController;

class RouterRegistrationTest extends TestCase
{
    private Router $router;

    public function setUp(): void
    {
        $container = new Container();
        $this->router = new Router($container);
    }

    public function testControllerWithAttributes()
    {
        $this->router->flush();
        $this->router->register(FakeAttributeController::class);

        $request = new ServerRequest('GET', '/prefixed/3-coucou-les-gens');
        $route = $this->router->match($request);
        $this->assertEquals('Method: GET, Id: 3, Slug: coucou-les-gens', $route->call($request));
        $this->assertEquals('fake.complex', $route->getName());

        $request = new ServerRequest('GET', '/prefixed/hello');
        $route = $this->router->match($request);
        $this->assertEquals('Hello', $route->call($request));
        $this->assertEquals('fake.hello', $route->getName());

        $request = new ServerRequest('GET', '/prefixed/static');
        $route = $this->router->match($request);
        $this->assertEquals('Hello', $route->call($request));
        $this->assertEquals('fake.static', $route->getName());

        $request = new ServerRequest('GET', '/prefixed');
        $route = $this->router->match($request);
        $this->assertEquals('Index', $route->call($request));
        $this->assertEquals('fake.index', $route->getName());
    }

    public function testFolderRegistration()
    {
        $this->router->flush();
        $this->router->registerDir(__DIR__ . DIRECTORY_SEPARATOR . 'Fake' . DIRECTORY_SEPARATOR . 'Controller', 'Tests\\Fake\\Controller');

        // Testing root controller

        $request = new ServerRequest('GET', '/folder/3-coucou-les-gens');
        $route = $this->router->match($request);
        $this->assertEquals('Method: GET, Id: 3, Slug: coucou-les-gens, Message: Hello World', $this->router->run($request));
        $this->assertEquals('folder.complex', $route->getName());

        $request = new ServerRequest('GET', '/folder/hello');
        $route = $this->router->match($request);
        $this->assertEquals('Hello', $this->router->run($request));
        $this->assertEquals('folder.hello', $route->getName());

        $request = new ServerRequest('GET', '/folder/static');
        $route = $this->router->match($request);
        $this->assertEquals('Hello', $this->router->run($request));
        $this->assertEquals('folder.static', $route->getName());

        $request = new ServerRequest('GET', '/folder');
        $route = $this->router->match($request);
        $this->assertEquals('Index', $this->router->run($request));
        $this->assertEquals('folder.index', $route->getName());

        // Testing sub folder

        $request = new ServerRequest('GET', '/sub/3-coucou-les-gens');
        $route = $this->router->match($request);
        $this->assertEquals('Method: GET, Id: 3, Slug: coucou-les-gens, Message: Hello World', $this->router->run($request));
        $this->assertEquals('sub.complex', $route->getName());

        $request = new ServerRequest('GET', '/sub/hello');
        $route = $this->router->match($request);
        $this->assertEquals('Hello', $this->router->run($request));
        $this->assertEquals('sub.hello', $route->getName());

        $request = new ServerRequest('GET', '/sub/static');
        $route = $this->router->match($request);
        $this->assertEquals('Hello', $this->router->run($request));
        $this->assertEquals('sub.static', $route->getName());

        $request = new ServerRequest('GET', '/sub');
        $route = $this->router->match($request);
        $this->assertEquals('Index', $this->router->run($request));
        $this->assertEquals('sub.index', $route->getName());
    }
}