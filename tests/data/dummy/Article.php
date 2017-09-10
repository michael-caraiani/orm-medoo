<?php

declare(strict_types=1);

namespace TiSuit\ORM\Tests\Dummy;

class Article extends \TiSuit\ORM\Entity
{
    public function getTable(): string
    {
        return 'articles';
    }

    public function getRelations(): array
    {
        return [
            'author' => [
                'entity' => 'user',
                'key' => 'author_id',
                'foreign_key' => 'id',
            ],
        ];
    }
}
