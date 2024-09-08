<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface BaseRepositoryInterface
{
    /**
     *
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes): mixed;

    /**
     * Insert new record(s).
     *
     * @param array $input
     *
     * @return bool
     */
    public function insert(array $input): bool;

    /**
     * Update model.
     *
     * @param Model $model
     * @param array $input
     *
     * @return ?Model
     */
    public function update(Model $model, array $input): ?Model;

    /**
     * Update or create model
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values): mixed;

    /**
     * Get the model detail by condition.
     *
     * @param array $condition
     * @param array $columns
     *
     * @return Builder
     */
    public function getByCondition($condition, array $columns = SELECT_ALL);

    /**
     * @param array $columns
     * @param $orderBy
     * @param $sortBy
     * @return mixed
     */
    public function all($columns, $orderBy, $sortBy);

    /**
     * Get the model detail.
     *
     * @param array $condition
     * @param array $columns
     * @param array $relations
     *
     * @return ?Model
     */
    public function getDetail(array $condition, array $columns = SELECT_ALL, array $relations = []): ?Model;

    /**
     * Find by id
     *
     * @param $id
     * @return mixed
     */
    public function find($id): mixed;

    /**
     * Find or fail by id
     *
     * @param $id
     * @return Model|null
     */
    public function findOneOrFail($id): ?Model;

    /**
     * Find by condition
     *
     * @param array $data
     * @return mixed
     */
    public function findBy(array $data, $columns): mixed;

    /**
     * Find one by condition
     *
     * @param array $data
     * @return mixed
     */
    public function findOneBy(array $data, $columns): mixed;

    /**
     * Delete all
     *
     * @return bool
     */
    public function deleteAll(): bool;

    /**
     * Delete with condition
     *
     * @param $attr
     * @param $value
     * @return mixed
     */
    public function deleteByAttr($attr, $value): mixed;

    /**
     * Delete record by id | list id
     *
     * @param array|int $id
     * @return ?int
     */
    public function delete(array|int $id): ?int;

    /**
     * Check the existent model.
     *
     * @param $condition
     * @param $column
     * @return bool
     */
    public function exist($column, $condition): bool;

    /**
     * @param $attr
     * @param $value
     * @return ?Model
     */
    public function findByAttrFirst($attr, $value);

    /**
     * @param $attr
     * @return \Illuminate\Support\Collection
     */
    public function pluckAttrId($attr);


    /**
     * @param $attr
     * @param $array
     * @return \Illuminate\Support\Collection
     */
    public function findByAttrInArray($attr, $array);

    /**
     * @param array $relations
     * @param array $data
     * @param array $columns
     * @param string $orderBy
     * @param string $sortBy
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function findByWithRelationship(
        array $relations,
        array $data,
        array $columns = SELECT_ALL,
        string $orderBy = FIELD_ID,
        string $sortBy = ORDER_ASC
    );

    /**
     * @param $relations
     * @param $columns
     * @param $orderBy
     * @param $sortBy
     * @return Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getAllWithRelationship(
        $relations = [''],
        $columns = SELECT_ALL,
        $orderBy = FIELD_ID,
        $sortBy = ORDER_ASC
    );

    /**
     * @param string $column
     * @param array $data
     * @param string $relations
     * @return Builder
     */
    public function whereIn(string $column, array $data, string $relations);

    /**
     * Get the list with relationship
     *
     * @param array $columns
     * @param array $condition
     * @param array $other sort, relation, join, paginate
     * @return mixed
     */
    public function getList(array $columns = SELECT_ALL, array $condition = [], array $other = []): mixed;

    /**
     * Upsert multiple models
     *
     * @param  array  $values
     * @param  array|string  $uniqueBy
     * @param  array|null  $update
     * @return Model
     */
    public function upsert($values, $uniqueBy, $update);
}
