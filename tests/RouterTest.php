<?php 

namespace Tests;

use Geekmusclay\Router\Core\Router;
use Geekmusclay\Router\Interfaces\RouterInterface;
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
        $this->assertEquals('hello', $route->call($request));
    }

    public function testPostMethod()
    {
        $this->router->flush();
        $request = new ServerRequest('POST', '/blog');
        $this->router->post('/blog', function () { return 'hello'; }, 'blog.post');
        $route = $this->router->match($request);
        $this->assertEquals('blog.post', $route->getName());
        $this->assertEquals('hello', $route->call($request));
    }

    public function testPutMethod()
    {
        $this->router->flush();
        $request = new ServerRequest('PUT', '/blog');
        $this->router->put('/blog', function () { return 'hello'; }, 'blog.put');
        $route = $this->router->match($request);
        $this->assertEquals('blog.put', $route->getName());
        $this->assertEquals('hello', $route->call($request));
    }

    public function testPatchMethod()
    {
        $this->router->flush();
        $request = new ServerRequest('PATCH', '/blog');
        $this->router->patch('/blog', function () { return 'hello'; }, 'blog.patch');
        $route = $this->router->match($request);
        $this->assertEquals('blog.patch', $route->getName());
        $this->assertEquals('hello', $route->call($request));
    }

    public function testDeleteMethod()
    {
        $this->router->flush();
        $request = new ServerRequest('DELETE', '/blog');
        $this->router->delete('/blog', function () { return 'hello'; }, 'blog.delete');
        $route = $this->router->match($request);
        $this->assertEquals('blog.delete', $route->getName());
        $this->assertEquals('hello', $route->call($request));
    }

    public function testGetMethodIfUrlDoesNotExists()
    {
        $this->router->flush();
        $request = new ServerRequest('GET', '/blog');
        $this->router->get('/coucou', function () { return 'hello'; }, 'blog');
        $route = $this->router->match($request);
        $this->assertEquals(null, $route);
    }

    public function testGetMethodWithParameters()
    {
        $this->router->flush();
        $request = new ServerRequest('GET', '/blog/mon-slug-8');

        $this->router->get('/blog/:slug-:id', function (string $slug, int $id) {
            return $slug . ' : ' . $id;
        }, 'post.show')->with([
            'slug' => '[a-z\-]+',
            'id' => '[0-9]+'
        ]);

        $route = $this->router->match($request);
        $this->assertEquals('post.show', $route->getName());
        $this->assertEquals('mon-slug : 8', $route->call($request));
        $this->assertEquals(['slug' => 'mon-slug', 'id' => '8'], $route->getMatches());
    }

    public function testGenerateUrl()
    {
        $this->router->flush();
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
        $this->router->flush();
        $this->router->get('/hello', function () { return 'hello'; }, 'hello');
        $route = $this->router->find('hello');
        $request = new ServerRequest('GET', '/hello');
        $this->assertEquals('hello', $route->getName());
        $this->assertEquals('hello', $route->call($request));
    }

    public function testNonStaticRoute()
    {
        $this->router->flush();
        $this->router->get('/fake', [FakeController::class, 'hello'], 'fake.hello');
        $request = new ServerRequest('GET', '/fake');
        $route = $this->router->match($request);
        $callable = $route->getCallback();
        $this->assertInstanceOf(FakeController::class, $callable[0]);
        $this->assertEquals('hello', $callable[1]);
        $this->assertEquals('Hello', $route->call($request));
    }

    public function testStaticRoute()
    {
        $this->router->flush();
        $this->router->get('/fake/static', FakeController::class . '::staticHello', 'fake.static.hello');
        $request = new ServerRequest('GET', '/fake/static');
        $route = $this->router->match($request);
        $this->assertEquals(FakeController::class . '::staticHello', $route->getCallback());
        $this->assertEquals('Hello', $route->call($request));
    }

    public function testRouteWithResquest()
    {
        $this->router->flush();
        $this->router->get('/fake/index/:id-:slug', [FakeController::class, 'index'], 'fake.index')->with([
            'slug' => '[a-zA-Z\-]+',
            'id' => '[0-9]+'
        ]);
        $request = new ServerRequest('GET', '/fake/index/3-coucou-les-gens');
        $route = $this->router->match($request);
        $callable = $route->getCallback();
        $this->assertInstanceOf(FakeController::class, $callable[0]);
        $this->assertEquals('index', $callable[1]);
        $this->assertEquals('Method: GET, Id: 3, Slug: coucou-les-gens', $route->call($request));

    }

    public function testRouteGroup()
    {
        $this->router->flush();
        $this->router->group('/api/v1', function (RouterInterface $group) {

            $group->get('/', function () {
                return 'index';
            }, 'api.v1.index');

            $group->get('/test', function () {
                return 'test';
            }, 'api.v1.test');

            $group->get('/:id', function (int $id) {
                return 'Sub n°' . $id;
            }, 'api.v1.detail')->with([
                'id' => '[0-9]+',
            ]);

        });

        $request = new ServerRequest('GET', '/api/v1');
        $route = $this->router->match($request);
        $this->assertEquals('api.v1.index', $route->getName());
        $this->assertEquals('index', $route->call($request));

        $request = new ServerRequest('GET', '/api/v1/test');
        $route = $this->router->match($request);
        $this->assertEquals('api.v1.test', $route->getName());
        $this->assertEquals('test', $route->call($request));

        $request = new ServerRequest('GET', '/api/v1/8');
        $route = $this->router->match($request);
        $this->assertEquals('api.v1.detail', $route->getName());
        $this->assertEquals('Sub n°8', $route->call($request));
    }
}