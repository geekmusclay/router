<?php 

declare(strict_types=1);

namespace Geekmusclay\Router\Attribute;

use Attribute;

#[Attribute]
class Route
{
    /** @var string $path The route path */
    private string $path;

    /** @var string $method The route HTTP method */
    private string $method;

    /** @var string|null $name The name of the route */
    private ?string $name;

    /** @param array|null $with Define specification for route params */
    private array $with;

    /**
     * Route atttribute constructor
     *
     * @param string         $path     The path of the route
     * @param array|callable $callable The route callable
     * @param string|null    $name     The route name
     */
    public function __construct(
        string $path,
        string $method = 'GET',
        ?string $name = null,
        ?array $with = []
    ) {
        $this->path = $path;
        $this->method = $method;
        $this->name = $name;
        $this->with = $with;
    }

    /**
     * Get the value of path
     */ 
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Set the value of path
     * 
     * @param string $path The route path
     */ 
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get the value of name
     */ 
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @param string $name The name of the route
     */ 
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the value of method
     */ 
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set the value of method
     *
     * @param string $method The route HTTP method
     */ 
    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get the value of with
     */ 
    public function getWith(): array
    {
        return $this->with;
    }

    /**
     * Set the value of with
     *
     * @param array $with The specifications for route params
     */ 
    public function setWith(array $with): self
    {
        $this->with = $with;

        return $this;
    }
}
