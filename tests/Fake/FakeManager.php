<?php

namespace Tests\Fake;

class FakeManager
{
    private FakeRepository $repository;

    public function __construct(FakeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getMessage(): string
    {
        return $this->repository->getMessage();
    }

    public function getRepository()
    {
        return $this->repository;
    }
}