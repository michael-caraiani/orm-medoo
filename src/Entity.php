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
        if (!isset($this->relationObjects[$name]) || empty($this->relationObjects[$name])) {
            $relation = $this->getRelations()[$name];
            if (!$relation || !$relation['entity'] || !$this->get($relation['key'] ?? 'id')) {
                return null;
            }

            $entity = $this->entity($entity);
            $key = $relation['key'] ?? 'id';
            $foreignKey = $relation['foreign_key'] ?? (($pos = strrpos(get_class($this), '\\')) ? substr(get_class($this), $pos + 1) : $pos).'_id';
            $type = $relation['type'] ?? 'has_one';
            $this->relationObjects[$name] = ($type === 'has_one') ? $entity->load($this->get($key), $foreignKey) : $entity->loadAll([$foreignKey => $this->get($key)]);
        }

        return $this->relationObjects[$name];
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
     *     'relation__name' => [
     *         'entity' => 'another_entity_name',
     *         'type' => 'has_one', //default, other options: has_many
     *         'key' => 'current_entity_key', //optional, default: id,
     *         'foreign_key' => 'another_entity_key', //optional, default '<current_entity>_id'
     *      ],
     * ];.
     *
     * //Example (current entity: blog post, another entity: user)
     * [
     *     'author' => [ //has_one
     *         'entity' => 'user',
     *         'key' => 'author_id',
     *         'foreign_key' => 'id'
     *     ],
     * ];
     * //This example can be called like $blogPostEntity->getAuthor()
     *
     * //Example (current entity: user, another entity: blog post)
     * [
     *     'posts' => [
     *         'entity' => 'post',
     *         'type' => 'has_many',
     *         'foreign_key' => 'author_id',
     *     ],
     * ]
     * //This example can be called like $userEntity->getPosts()
     * </code>
     *
     * @return array
     */
    abstract public function getRelations(): array;
}
