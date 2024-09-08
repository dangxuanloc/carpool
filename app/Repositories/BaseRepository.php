<?php

namespace App\Repositories;

use App\Helpers\CommonHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

abstract class BaseRepository implements BaseRepositoryInterface
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * BaseRepository constructor.
     * @param Model $model
     */
    public function __construct()
    {
        $this->setModel();
    }

    public function setModel()
    {
        $this->model = app()->make($this->getModel());
    }

    abstract public function getModel();

    /**
     * Create new model.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function create(array $input): mixed
    {
        try {
            $newModel = new $this->model($input);
            $newModel->save();
        } catch (\Exception $exception) {
            Log::error('[Create]: ' . $exception->getMessage());
            $newModel = null;
        }

        return $newModel;
    }

    /**
     * Insert new record(s).
     *
     * @param array $values
     *
     * @return bool
     */
    public function insert(array $values): bool
    {
        return $this->model->newQuery()->insert($values);
    }

    /**
     * Update model.
     *
     * @param Model $model
     * @param array $input
     *
     * @return Model|null
     */
    public function update(Model $model, array $input): ?Model
    {
        try {
            foreach ($input as $attribute => $value) {
                $model->{$attribute} = $value;
            }
            if ($model->isDirty()) {
                $model->save();
            }
        } catch (\Exception $exception) {
            $model = null;
            Log::error('[Update]: ' . $exception->getMessage());
            $model = null;
        }

        return $model;
    }

    /**
     * Create or update a record matching the attributes, and fill it with values.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return mixed
     */
    public function updateOrCreate(array $attributes, array $values): mixed
    {
        try {
            $updateOrCreate = $this->model->newQuery()->updateOrCreate($attributes, $values);
        } catch (\Exception $exception) {
            Log::error('[updateOrCreate]: ' . $exception->getMessage());
            $updateOrCreate = null;
        }

        return $updateOrCreate;
    }

    /**
     * Get the model detail by condition.
     *
     * @param $condition
     * @param array $columns
     *
     * @return Builder
     */
    public function getByCondition($condition, array $columns = SELECT_ALL)
    {
        return $this->model
            ->newQuery()
            ->select($columns)
            ->where($condition);
    }

    /**
     * Get the model detail.
     *
     * @param array $condition
     * @param array $columns
     * @param array $relations
     *
     * @return ?Model
     */
    public function getDetail(array $condition, array $columns = SELECT_ALL, array $relations = []): ?Model
    {
        $query = $this->getClause($this->model->newQuery(), $condition);
        if ($relations) {
            $query = $this->relate($query, $relations);
        }

        return $query->first($columns);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function find($id): mixed
    {
        return $this->model->find($id);
    }

    /**
     * @param array $data
     * @param $columns
     * @return mixed
     */
    public function findBy(array $data, $columns = SELECT_ALL): mixed
    {
        return $this->model->where($data)->select($columns);
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function findOneBy(array $data, $columns = SELECT_ALL): mixed
    {
        return $this->model
            ->select($columns)
            ->where($data)
            ->first();
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function deleteAll(): bool
    {
        return $this->model->delete();
    }

    /**
     * @param $attr
     * @param $value
     * @return false
     */
    public function deleteByAttr($attr, $value): mixed
    {
        return !is_null($attr) ? $this->model::where($attr, $value)->delete() : false;
    }

    /**
     * Delete record by id | list id
     *
     * @param array|int $id
     * @return int|null
     */
    public function delete(array|int $id): ?int
    {
        try {
            $query = $this->model->destroy($id);
            // Count equal to 0
            if (empty($query)) {
                $query = null;
            }
        } catch (\Exception $exception) {
            Log::error('[Delete]: ' . $exception->getMessage());
            $query = null;
        }

        return $query;
    }

    /**
     * Check the existent model.
     *
     * @param $condition
     * @param $column
     * @return bool
     */
    public function exist($column, $condition): bool
    {
        return $this->model->where($column, $condition)->exists();
    }

    /**
     * @param array $columns
     * @param $orderBy
     * @param $sortBy
     * @return mixed
     */
    public function all($columns = ['*'], $orderBy = 'id', $sortBy = 'asc')
    {
        return $this->model->orderBy($orderBy, $sortBy)->get($columns);
    }

    /**
     * @param $attr
     * @param $value
     * @return ?Model
     */
    public function findByAttrFirst($attr, $value)
    {
        return !is_null($attr) ? $this->model::where($attr, $value)->first() : null;
    }

    /**
     * @param $attr
     * @param $array
     * @return \Illuminate\Support\Collection
     */
    public function findByAttrInArray($attr, $array)
    {
        return !is_null($attr) ? $this->model::whereIn($attr, $array)->get() : collect([]);
    }

    /**
     * @param $attr
     * @return \Illuminate\Support\Collection
     */
    public function pluckAttrId($attr)
    {
        return !is_null($attr) ? $this->model::pluck($attr, 'id')->all() : collect([]);
    }

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
    ) {
        return $this->model->with($relations)->orderBy($orderBy, $sortBy)->get($columns);
    }

    /**
     * @param string $orderBy
     * @param string $sortBy
     * @param array $data
     * @return mixed
     */
    public function orderBy(string $orderBy, string $sortBy, array $data)
    {
        return $this->model->where($data)->orderBy($orderBy, $sortBy)->get();
    }

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
    ) {
        return $this->model->with($relations)->where($data)->orderBy($orderBy, $sortBy)->get($columns);
    }

    /**
     * @param string $column
     * @param array $data
     * @param string $relations
     * @return Builder
     */
    public function whereIn(string $column, array $data, string $relations)
    {
        return $this->model->with($relations)->whereIn($column, $data);
    }

    /**
     * Get the list with relationship
     *
     * @param array $columns
     * @param array $condition
     * @param array $other sort, relation, join, paginate, filter
     * @return mixed
     */
    public function getList(array $columns = SELECT_ALL, array $condition = [], array $other = []): mixed
    {
        $query = $this->getClause($this->model->newQuery(), $condition);
        $query->select($columns);
        $other = $this->moveItemToLast($other, KEY_PAGINATE);
        foreach ($other as $key => $value) {
            $query = $this->{$key}($query, $value);
        }

        return $query;
    }

    /**
     * Get clause And.
     *
     * @param Builder $query
     * @param array $condition
     *
     * @return Builder
     */
    public function getClause(Builder $query, array $condition): Builder
    {
        foreach ($condition as $column => $value) {
            if (isset($value[KEY_VALUE])) {
                switch ($value[KEY_OPERATOR]) {
                    case KEY_OR_WHERE_IN:
                        $query->orWhereIn($column, $value[KEY_VALUE]);
                        break;
                    case KEY_OR_WHERE_NOT_IN:
                        $query->orWhereNotIn($column, $value[KEY_VALUE]);
                        break;
                    case KEY_OR_WHERE_BETWEEN:
                        $query->orWhereBetween($column, $value[KEY_VALUE]);
                        break;
                    case KEY_OR_WHERE_NOT_BETWEEN:
                        $query->orWhereNotBetween($column, $value[KEY_VALUE]);
                        break;
                    case KEY_OR_WHERE_NULL:
                        $query->orWhereNull($column);
                        break;
                    case KEY_OR_WHERE_NOT_NULL:
                        $query->orWhereNotNull($column);
                        break;
                    case KEY_OR_WHERE:
                        $query->orWhere($column, $value[KEY_VALUE]);
                        break;
                    case KEY_WHERE_IN:
                        $query->whereIn($column, $value[KEY_VALUE]);
                        break;
                    case KEY_WHERE_NOT_IN:
                        $query->whereNotIn($column, $value[KEY_VALUE]);
                        break;
                    case KEY_WHERE_BETWEEN:
                        $query->whereBetween($column, $value[KEY_VALUE]);
                        break;
                    case KEY_WHERE_NOT_BETWEEN:
                        $query->whereNotBetween($column, $value[KEY_VALUE]);
                        break;
                    case KEY_WHERE_NULL:
                        $query->whereNull($column);
                        break;
                    case KEY_WHERE_NOT_NULL:
                        $query->whereNotNull($column);
                        break;
                    case KEY_WHERE_DATE:
                        $query->whereDate($column, OPERATOR_EQUAL, $value[KEY_VALUE]);
                        break;
                    case KEY_WHERE_DATE_LESS:
                        $query->whereDate($column, OPERATOR_LESS_EQUAL, $value[KEY_VALUE]);
                        break;
                    case KEY_WHERE_HAS:
                        $query->whereHas($value[KEY_RELATIONSHIP_NAME], function ($q) use ($value, $column) {
                            $q->where($column, $value[KEY_VALUE]);
                        });
                        break;
                    case KEY_WHERE_HAS_LIKE:
                        $query->whereHas($value[KEY_RELATIONSHIP_NAME], function ($q) use ($value, $column) {
                            $q->where($column, OPERATOR_LIKE, '%' . CommonHelper::escapeStr($value[KEY_VALUE]) . '%');
                        });
                        break;
                    case KEY_WHERE_IN_VALUE_AND_NULL:
                        $query->where(function ($q) use ($value, $column) {
                            $q->whereIn($column, $value[KEY_VALUE])
                                ->orWhereNull($column);
                        });
                        break;
                    case KEY_WHERE_IN_VALUE_AND_NOT_NULL:
                        $query->orWhere(function ($q) use ($value, $column) {
                            $q->orWhere($column, $value[KEY_VALUE])
                                ->whereNotNull($column);
                        });
                        break;
                    case KEY_LIKE_OR_WHERE:
                        $query->orWhere($column, OPERATOR_LIKE, '%' . CommonHelper::escapeStr($value[KEY_VALUE]) . '%');
                        break;
                    case KEY_LIKE_WHERE:
                        $query->where($column, OPERATOR_LIKE, '%' . CommonHelper::escapeStr($value[KEY_VALUE]) . '%');
                        break;
                    case KEY_WHERE_HAS_BETWEEN:
                        $query->whereHas($value[KEY_RELATIONSHIP_NAME], function ($q) use ($value, $column) {
                            $q->whereBetween($column, CommonHelper::dateBetween($value[KEY_VALUE]));
                        });
                        break;
                    case KEY_CASE_WHERE_NULL_OR_BETWEEN:
                        $query->where(function ($q) use ($value) {
                            $q->whereRaw(
                                '(CASE
                                    WHEN customers.leave_at IS NOT NULL
                                    THEN DATE(customers.entry_at) <= ? AND DATE(customers.leave_at) >= ?
                                    ELSE DATE(customers.entry_at) <= ?
                                END)',
                                [$value['value'], $value['value'], $value['value']]
                            );
                        });
                        break;
                    default:
                        $query->where($column, $value[KEY_OPERATOR], $value[KEY_VALUE]);
                        break;
                }
            }
        }

        return $query;
    }

    /**
     * Move item to the last index of array.
     *
     * @param array $input
     * @param $key
     * @return array
     */
    public static function moveItemToLast(array $input, $key): array
    {
        if (count($input) > 1 && array_key_exists($key, $input)) {
            $valueOfKeyInArray = $input[$key];
            unset($input[$key]);
            $input += [
                $key => $valueOfKeyInArray
            ];
        }

        return $input;
    }

    /**
     * Relate relationship.
     *
     * @param Builder $query
     * @param array $relations
     *
     * @return Builder
     */
    protected function relate(Builder $query, array $relations): Builder
    {
        foreach ($relations as $relation) {
            $query->with([$relation[KEY_RELATIONSHIP_NAME] => function ($query) use ($relation) {
                $query->select($relation[KEY_RELATIONSHIP_SELECT]);
            }]);
        }

        return $query;
    }

    /**
     * Join other table.
     *
     * @param Builder $query
     * @param array $join
     * @param null $type
     * @return Builder
     */
    protected function join(Builder $query, array $join, $type = null)
    {
        foreach ($join as $value) {
            $query->join(
                $value[KEY_TABLE],
                $value[KEY_FOREIGN_KEY],
                OPERATOR_EQUAL,
                $value[KEY_PRIMARY_KEY],
                $type ?? $value[KEY_TYPE_JOIN]
            );
        }

        return $query;
    }

    /**
     * Filter models.
     *
     * @param Builder $query
     * @param array $filter
     * @return Builder
     */
    protected function filter(Builder $query, array $filter): Builder
    {
        return $query->filter($filter);
    }

    /**
     *
     * @return LengthAwarePaginator
     */
    protected function paginate(Builder $query, array $pagination)
    {
        return $query->paginate(
            $pagination[INPUT_PAGE_SIZE],
            SELECT_ALL,
            INPUT_PAGE,
            $pagination[INPUT_PAGE]
        );
    }

    /**
     * Sort the list of models.
     *
     * @param  Builder  $query
     * @param  array  $sort
     * @return Builder
     */
    protected function sort(Builder $query, array $sort): Builder
    {
        foreach ($sort as $column => $value) {
            $query->orderBy($column, $value);
        }

        return $query;
    }

    /**
     * Find one or fail model
     *
     * @param  int  $id
     * @param  array  $condition,
     * @param  array  $columns
     * @param  array  $relations
     * @return Model|null
     */
    public function findOneOrFail($id, $columns = SELECT_ALL, $condition = [], $relations = []): ?Model
    {
        $query = $this->getClause($this->model->newQuery(), $condition);
        if ($relations) {
            return $this->relate($query, $relations)->findOrFail($id, $columns);
        }

        return $this->model->findOrFail($id, $columns);
    }

    /**
     * Upsert multiple models
     *
     * @param  array  $values
     * @param  array|string  $uniqueBy
     * @param  array|null  $update
     * @return Model
     */
    public function upsert($values, $uniqueBy, $update)
    {
        return $this->model->upsert($values, $uniqueBy, $update);
    }
}
