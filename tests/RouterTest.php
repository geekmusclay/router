<?php 

namespace Tests;

use Geekmusclay\Router\Router;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Tests\Fake\FakeController;

class RouterTest extends TestCase
{
    private Router $router;

    public function setUp(): void
    {
        $this->router = new Router();
    }

    public function testGetMethod()
    {
        $request = new ServerRequest('GET', '/blog');
        $this->router->get('/blog', function () { return 'hello'; }, 'blog');
        $route = $this->router->match($request);
        $this->assertEquals('blog', $route->getName());
        $this->assertEquals('hello', call_user_func_array($route->getCallback(), [$request]));
    }

    public function testGetMethodIfUrlDoesNotExists()
    {
        $request = new ServerRequest('GET', '/blog');
        $this->router->get('/coucou', function () { return 'hello'; }, 'blog');
        $route = $this->router->match($request);
        $this->assertEquals(null, $route);
    }

    public function testGetMethodWithParameters()
    {
        $request = new ServerRequest('GET', '/blog/mon-slug-8');

        $this->router->get('/blog/:slug-:id', function (string $slug, int $id) {
            return $slug . ' : ' . $id;
        }, 'post.show')->with([
            'slug' => '[a-z\-]+',
            'id' => '[0-9]+'
        ]);

        $route = $this->router->match($request);
        $this->assertEquals('post.show', $route->getName());
        $this->assertEquals('mon-slug : 8', $route->call());
        $this->assertEquals(['slug' => 'mon-slug', 'id' => '8'], $route->getMatches());
    }

    public function testGenerateUrl()
    {
        $this->router->get('/blog/:slug-:id', function () {
            return 'hello';
        }, 'post.show')->with([
            'slug' => '[a-z\-]+',
            'id' => '[0-9]+'
        ]);
        $this->assertEquals('/blog/mon-slug-8', $this->router->path('post.show', [
            'slug' => 'mon-slug',
            'id' => 8
        ]));
    }

    public function testFindRoute()
    {
        $this->router->get('/hello', function () { return 'hello'; }, 'hello');
        $route = $this->router->find('hello');
        $request = new ServerRequest('GET', '/hello');
        $this->assertEquals('hello', $route->getName());
        $this->assertEquals('hello', call_user_func_array($route->getCallback(), [$request]));
    }

    public function testNonStaticRoute()
    {
        $this->router->get('/fake', [FakeController::class, 'hello'], 'fake.hello');
        $request = new ServerRequest('GET', '/fake');
        $route = $this->router->match($request);
        $callable = $route->getCallback();
        $this->assertInstanceOf(FakeController::class, $callable[0]);
        $this->assertEquals('hello', $callable[1]);
        $this->assertEquals('Hello', $route->call());
    }

    public function testStaticRoute()
    {
        $this->router->get('/fake/static', FakeController::class . '::staticHello', 'fake.static.hello');
        $request = new ServerRequest('GET', '/fake/static');
        $route = $this->router->match($request);
        $this->assertEquals(FakeController::class . '::staticHello', $route->getCallback());
        $this->assertEquals('Hello', $route->call());
    }
}