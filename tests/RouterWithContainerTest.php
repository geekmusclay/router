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
}
