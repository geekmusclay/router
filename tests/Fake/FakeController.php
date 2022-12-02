<?php 

namespace Tests\Fake;

use Psr\Http\Message\ServerRequestInterface as Request;

class FakeController
{
    public function hello()
    {
        return 'Hello';
    }

    public static function staticHello()
    {
        return 'Hello';
    }

    public function index(Request $request, int $id, string $slug)
    {
        return 'Method: ' . $request->getMethod() . ', Id: ' . $id . ', Slug: ' . $slug;
    }
}