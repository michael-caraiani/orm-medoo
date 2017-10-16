<?php

declare(strict_types=1);

namespace TiSuit\ORM\Tests\Dummy;

class DummyEntity extends \TiSuit\ORM\Entity
{
    /**
     * {@inheritdoc}
     */
    public function getTable(): string
    {
        return 'dummy';
    }

    /**
     * {@inheritdoc}
     */
    public function getValidators(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getRelations(): array
    {
        return [];
    }
}
