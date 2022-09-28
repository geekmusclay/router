<?php 

use Geekmusclay\Router\Router;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

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
        $this->expectException(Exception::class);
        $request = new ServerRequest('GET', '/blog');
        $this->router->get('/coucou', function () { return 'hello'; }, 'blog');
        $this->router->match($request);
    }

    public function testGetMethodWithParameters()
    {
        $request = new ServerRequest('GET', '/blog/mon-slug-8');

        $this->router->get('/blog/:slug-:id', function () {
            return 'hello';
        }, 'post.show')->with([
            'slug' => '[a-z\-]+',
            'id' => '[0-9]+'
        ]);

        $route = $this->router->match($request);
        $this->assertEquals('post.show', $route->getName());
        $this->assertEquals('hello', call_user_func_array($route->getCallback(), [$request]));
        $this->assertEquals(['mon-slug', '8'], $route->getMatches());
    }
}