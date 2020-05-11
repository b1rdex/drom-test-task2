<?php

declare(strict_types=1);

namespace Acme;

class Comment
{
    private int $id;
    private string $name;
    private string $text;

    public function __construct(int $id, string $name, string $text)
    {
        $this->id = $id;
        $this->name = $name;
        $this->text = $text;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
