<?php 

namespace Tests\Fake;

use Geekmusclay\Router\Attribute\Route;
use Psr\Http\Message\ServerRequestInterface as Request;

#[Route(path: '/prefixed')]
class FakeAttributeController
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