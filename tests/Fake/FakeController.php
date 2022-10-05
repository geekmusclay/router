<?php 

namespace Tests\Fake;

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
}