<?php

use PHPUnit\Framework\TestCase;
use Geekmusclay\DI\Core\Container;
use GuzzleHttp\Psr7\ServerRequest;
use Geekmusclay\Router\Core\Router;
use Tests\Fake\FakeAttributeController;
use Tests\Fake\FakeComplexController;

class RouterWithContainerTest extends TestCase
{
    private Router $router;

    public function setUp(): void
    {
        $container = new Container();
        $this->router = new Router($container);
    }

    public function testComplexController()
    {
        $this->router->get('/complex/:id-:slug', [FakeComplexController::class, 'index'])->with([
            'id' => '[0-9]+',
            'slug' => '[a-z\-]+'
        ]);
        $request = new ServerRequest('GET', '/complex/3-coucou-les-gens');
        $this->assertEquals('Method: GET, Id: 3, Slug: coucou-les-gens, Message: Hello World', $this->router->run($request));
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
}