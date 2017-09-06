<?php

declare(strict_types=1);

namespace TiSuit\ORM;

abstract class Entity extends \TiSuit\Core\Root
{
    protected $relationObjects = [];

    public function __call(?string $method = null, array $params = [])
    {
        $parts = preg_split('/([A-Z][^A-Z]*)/', $method, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
        $type = array_shift($parts);
        $relation = strtolower(implode('_', $parts));

        if ($type === 'get' && in_array($relation, $this->getRelations(), true)) {
            return $this->loadRelation($relation);
        }

        return parent::__call($method, $params);
    }

    /**
     * Return all entity data as array.
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Set all data to entity.
     *
     * @param array $data
     *
     * @return \TiSuit\ORM\Entity
     */
    public function setData(array $data)
    {
        $this->data = array_merge($this->data, $data);

        return $this;
    }

    /**
     * Save entity data in db.
     *
     * @return \TiSuit\ORM\Entity
     */
    public function save()
    {
        if ($this->getId()) {
            $this->medoo->update($this->getTable(), $this->data, ['id' => $this->getId()]);
        } else {
            $this->medoo->insert($this->getTable(), $this->data);
            $this->setId($this->medoo->id());
        }

        return $this;
    }

    /**
     * Load entity (data from db).
     *
     * @param mixed  $value  Field value (eg: id field with value = 10)
     * @param string $field  Field name, default: id
     * @param array  $fields Fields (columns) to load, default: all
     *
     * @return \TiSuit\ORM\Entity
     */
    public function load($value, $field = 'id', array $fields = null): \TiSuit\ORM\Entity
    {
        $data = $this->medoo->get($this->getTable(), $fields ?? '*', [$field => $value]);
        $this->data = $data[0] ?? [];

        return $this;
    }

    /**
     * Get all entities from db.
     *
     * @param array $where Where clause
     *
     * @return \Slim\Collection
     */
    public function loadAll(array $where = []): \Slim\Collection
    {
        $allData = $this->medoo->select($this->getTable(), '*', $where);
        $items = [];
        foreach ($allData as $data) {
            $items[] = $this->container->get('entity_'.get_class($this))->setData($data);
        }

        return new \Slim\Collection($items);
    }

    /**
     * Load realated entity by relation name.
     *
     * @param string $name Relation name
     *
     * @return null|\TiSuit\ORM\Entity
     */
    public function loadRelation(string $name): ?\TiSuit\ORM\Entity
    {
        if (!isset($this->relations[$name]) || empty($this->relations[$name])) {
            [$entity, $columns] = $this->getRelations()[$name] ?? [null, []];
            $localColumn = $columns[0] ?? null;
            $foreignColumn = $columns[1] ?? null;
            if (!$entity || !$this->get($localColumn)) {
                return null;
            }

            $this->relations[$name] = $this->entity($entity)->load($this->get($localColumn), $foreignColumn);
        }

        return $this->relations[$name];
    }

    /**
     * Determine whether the target data existed.
     *
     * @param array $where
     *
     * @return bool
     */
    public function has(array $where = []): bool
    {
        return $this->medoo->has($this->getTable(), $where);
    }

    /**
     * Get count of items by $where conditions.
     *
     * @param array $where Where clause
     *
     * @return int
     */
    public function count(array $where = []): int
    {
        return $this->medoo->count($this->getTable(), $where);
    }

    /**
     * Delete entity row from db.
     *
     * @return bool
     */
    public function delete(): bool
    {
        return (bool) $this->medoo->delete($this->getTable(), ['id' => $this->getId()]);
    }

    /**
     * Return entity table name.
     *
     * @return string
     */
    abstract public function getTable(): string;

    /**
     * Return array of entity relations
     * <code>
     * //structure
     * [
     *     'relation__name' => ['another_entity_name', 'current_entity_column' => 'another_entity_column'],
     * ];.
     *
     * //Example (current entity: blog post, another entity: user)
     * [
     *     'author' => ['user', 'author_id' => 'id']
     * ];
     * //This example can be called like $blogPostEntity->getAuthor()
     * </code>
     *
     * @return array
     */
    abstract public function getRelations(): array;
}
