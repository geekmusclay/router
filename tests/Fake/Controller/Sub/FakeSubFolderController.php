<?php

namespace Tests\Fake\Controller\Sub;

use Tests\Fake\FakeManager;
use Geekmusclay\Router\Attribute\Route;
use Psr\Http\Message\ServerRequestInterface as Request;

#[Route(path: '/sub')]
class FakeSubFolderController
{
    private FakeManager $manager;

    public function __construct(FakeManager $manager)
    {
        $this->manager = $manager;
    }

    #[Route(path: '/', name: 'sub.index')]
    public function index()
    {
        return 'Index';
    }

    #[Route(path: '/hello', name: 'sub.hello')]
    public function hello()
    {
        return 'Hello';
    }

    #[Route(path: '/static', name: 'sub.static')]
    public static function staticHello()
    {
        return 'Hello';
    }

    #[Route(path: '/:id-:slug', name: 'sub.complex', with: [
        'id' => '[0-9]+',
        'slug' => '[a-z\-]+'
    ])]
    public function complex(Request $request, int $id, string $slug)
    {
        $message = $this->manager->getMessage();

        return 'Method: ' . $request->getMethod() . ', Id: ' . $id . ', Slug: ' . $slug . ', Message: ' . $message;
    }
}
