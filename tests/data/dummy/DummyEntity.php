<?php

declare(strict_types=1);

namespace TiSuit\ORM\Tests\Dummy;

class DummyEntity extends \TiSuit\ORM\Entity
{
    public function getTable(): string
    {
        return 'dummy';
    }

    public function getRelations(): array
    {
        return [];
    }
}
