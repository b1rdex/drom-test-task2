<?php

declare(strict_types=1);

namespace Acme;

class CommentFactory
{
    /**
     * @param array<array{id: int, name: string, text: string}> $list
     *
     * @return Comment[]
     */
    public function fromList(array $list): array
    {
        $comments = [];
        foreach ($list as ['id' => $id, 'name' => $name, 'text' => $text]) {
            $comments[] = new Comment($id, $name, $text);
        }

        return $comments;
    }
}
