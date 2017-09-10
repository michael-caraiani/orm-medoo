<?php

declare(strict_types=1);

namespace TiSuit\ORM\Tests\Dummy;

class User extends \TiSuit\ORM\Entity
{
    public function getTable(): string
    {
        return 'users';
    }

    public function getRelations(): array
    {
        return [
            'articles' => [
                'entity' => 'article',
                'type' => 'has_many',
                'foreign_key' => 'author_id',
            ],
            'error_relation' => [],
        ];
    }
}
