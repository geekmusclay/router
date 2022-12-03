<?php

namespace Tests\Fake;

use Psr\Http\Message\ServerRequestInterface as Request;

class FakeComplexController
{
    private FakeManager $manager;

    public function __construct(FakeManager $manager)
    {
        $this->manager = $manager;
    }

    public function index(Request $request, int $id, string $slug): string
    {
        $message = $this->manager->getMessage();

        return 'Method: ' . $request->getMethod() . ', Id: ' . $id . ', Slug: ' . $slug . ', Message: ' . $message;
    }

    public function getManager()
    {
        return $this->manager;
    }
}