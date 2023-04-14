<?php

namespace Tests\Fake\Controller;

use Tests\Fake\FakeManager;
use Geekmusclay\Router\Attribute\Route;
use Psr\Http\Message\ServerRequestInterface as Request;

#[Route(path: '/folder')]
class FakeFolderController
{
    private FakeManager $manager;

    public function __construct(FakeManager $manager)
    {
        $this->manager = $manager;
    }

    #[Route(path: '/', name: 'folder.index')]
    public function index()
    {
        return 'Index';
    }

    #[Route(path: '/hello', name: 'folder.hello')]
    public function hello()
    {
        return 'Hello';
    }

    #[Route(path: '/static', name: 'folder.static')]
    public static function staticHello()
    {
        return 'Hello';
    }

    #[Route(path: '/:id-:slug', name: 'folder.complex', with: [
        'id' => '[0-9]+',
        'slug' => '[a-z\-]+'
    ])]
    public function complex(Request $request, int $id, string $slug)
    {
        $message = $this->manager->getMessage();

        return 'Method: ' . $request->getMethod() . ', Id: ' . $id . ', Slug: ' . $slug . ', Message: ' . $message;
    }
}
