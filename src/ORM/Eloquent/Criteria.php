<?php
namespace Idunis\Context\ORM\Eloquent;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Criteria
{
    public static function applyOnSearch(array $requestCriteria)
    {

    }

    public static function apply(array $requestCriteria)
    {
        $instance = new static;

        $fieldsSearchable = $instance->getFieldsSearchable();
        $search = Arr::get($requestCriteria, 'search', null);
        $searchFields = Arr::get($requestCriteria, 'searchFields', null);
        $filter = Arr::get($requestCriteria, 'filter', null);
        $orderBy = Arr::get($requestCriteria, 'orderBy', null);
        $sortedBy = Arr::get($requestCriteria, 'sortedBy', 'asc');
        $with = Arr::get($requestCriteria, 'with', null);
        $withCount = Arr::get($requestCriteria, 'withCount', null);
        $searchJoin = Arr::get($requestCriteria, 'searchJoin', null);
        $sortedBy = !empty($sortedBy) ? $sortedBy : 'asc';

        if ($search && is_array($fieldsSearchable) && count($fieldsSearchable)) {

            $searchFields = is_array($searchFields) || is_null($searchFields) ? $searchFields : explode(';', $searchFields);
            $fields = $instance->parserFieldsSearch($fieldsSearchable, $searchFields);
            $isFirstField = true;
            $searchData = $instance->parserSearchData($search);
            $search = $instance->parserSearchValue($search);
            $modelForceAndWhere = strtolower($searchJoin) === 'and';

            $instance = $instance->where(function ($query) use ($fields, $search, $searchData, $isFirstField, $modelForceAndWhere) {
                
                foreach ($fields as $field => $condition) {

                    if (is_numeric($field)) {
                        $field = $condition;
                        $condition = "=";
                    }

                    $value = null;

                    $condition = trim(strtolower($condition));

                    if (isset($searchData[$field])) {
                        $value = ($condition == "like" || $condition == "ilike") ? "%{$searchData[$field]}%" : $searchData[$field];
                    } else {
                        if (!is_null($search) && !in_array($condition,['in','between'])) {
                            $value = ($condition == "like" || $condition == "ilike") ? "%{$search}%" : $search;
                        }
                    }

                    $relation = null;
                    if(stripos($field, '.')) {
                        $explode = explode('.', $field);
                        $field = array_pop($explode);
                        $relation = implode('.', $explode);
                    }
                    if($condition === 'in'){
                        $value = explode(',',$value);
                        if( trim($value[0]) === "" || $field == $value[0]){
                            $value = null;
                        }
                    }
                    if($condition === 'between'){
                        $value = explode(',',$value);
                        if(count($value) < 2){
                            $value = null;
                        }
                    }
                    $modelTableName = $query->getModel()->getTable();
                    if ( $isFirstField || $modelForceAndWhere ) {
                        if (!is_null($value)) {
                            if(!is_null($relation)) {
                                $query->whereHas($relation, function($query) use($field,$condition,$value) {
                                    if($condition === 'in'){
                                        $query->whereIn($field,$value);
                                    }elseif($condition === 'between'){
                                        $query->whereBetween($field,$value);
                                    }else{
                                        $query->where($field,$condition,$value);
                                    }
                                });
                            } else {
                                if($condition === 'in'){
                                    $query->whereIn($modelTableName.'.'.$field,$value);
                                }elseif($condition === 'between'){
                                    $query->whereBetween($modelTableName.'.'.$field,$value);
                                }else{
                                    $query->where($modelTableName.'.'.$field,$condition,$value);
                                }
                            }
                            $isFirstField = false;
                        }
                    } else {
                        if (!is_null($value)) {
                            if(!is_null($relation)) {
                                $query->orWhereHas($relation, function($query) use($field,$condition,$value) {
                                    if($condition === 'in'){
                                        $query->whereIn($field,$value);
                                    }elseif($condition === 'between'){
                                        $query->whereBetween($field, $value);
                                    }else{
                                        $query->where($field,$condition,$value);
                                    }
                                });
                            } else {
                                if($condition === 'in'){
                                    $query->orWhereIn($modelTableName.'.'.$field, $value);
                                }elseif($condition === 'between'){
                                    $query->whereBetween($modelTableName.'.'.$field,$value);
                                }else{
                                    $query->orWhere($modelTableName.'.'.$field, $condition, $value);
                                }
                            }
                        }
                    }
                }
            });
        }

        if (isset($orderBy) && !empty($orderBy)) {
            $orderBySplit = explode(';', $orderBy);
            if(count($orderBySplit) > 1) {
                $sortedBySplit = explode(';', $sortedBy);
                foreach ($orderBySplit as $orderBySplitItemKey => $orderBySplitItem) {
                    $sortedBy = isset($sortedBySplit[$orderBySplitItemKey]) ? $sortedBySplit[$orderBySplitItemKey] : $sortedBySplit[0];
                    $instance->parserFieldsOrderBy($orderBySplitItem, $sortedBy);
                }
            } else {
                $instance->parserFieldsOrderBy($orderBySplit[0], $sortedBy);
            }
        }

        if (isset($filter) && !empty($filter)) {
            if (is_string($filter)) {
                $filter = explode(';', $filter);
            }

            $instance->select($filter);
        }

        if ($with) {
            $with = explode(';', $with);
            $model = $model->with($with);
        }

        if ($withCount) {
            $withCount = explode(';', $withCount);
            $instance->withCount($withCount);
        }
        
        return $instance;
    }

    /**
     * @param $model
     * @param $orderBy
     * @param $sortedBy
     * @return mixed
     */
    protected function parserFieldsOrderBy($orderBy, $sortedBy)
    {
        $split = explode('|', $orderBy);
        if(count($split) > 1) {
            /*
             * ex.
             * products|description -> join products on current_table.product_id = products.id order by description
             *
             * products:custom_id|products.description -> join products on current_table.custom_id = products.id order
             * by products.description (in case both tables have same column name)
             */
            $table = $this->getModel()->getTable();
            $sortTable = $split[0];
            $sortColumn = $split[1];

            $split = explode(':', $sortTable);
            $localKey = '.id';
            if (count($split) > 1) {
                $sortTable = $split[0];

                $commaExp = explode(',', $split[1]);
                $keyName = $table.'.'.$split[1];
                if (count($commaExp) > 1) {
                    $keyName = $table.'.'.$commaExp[0];
                    $localKey = '.'.$commaExp[1];
                }
            } else {
                /*
                 * If you do not define which column to use as a joining column on current table, it will
                 * use a singular of a join table appended with _id
                 *
                 * ex.
                 * products -> product_id
                 */
                $prefix = Str::singular($sortTable);
                $keyName = $table.'.'.$prefix.'_id';
            }

            $this
                ->leftJoin($sortTable, $keyName, '=', $sortTable.$localKey)
                ->orderBy($sortColumn, $sortedBy)
                ->addSelect($table.'.*');
        } else {
            $this->orderBy($orderBy, $sortedBy);
        }
        return $this;
    }

    /**
     * @param $search
     *
     * @return array
     */
    protected function parserSearchData($search)
    {
        $searchData = [];

        if (stripos($search, ':')) {
            $fields = explode(';', $search);

            foreach ($fields as $row) {
                try {
                    list($field, $value) = explode(':', $row);
                    $searchData[$field] = $value;
                } catch (\Exception $e) {
                    //Surround offset error
                }
            }
        }

        return $searchData;
    }

    /**
     * @param $search
     *
     * @return null
     */
    protected function parserSearchValue($search)
    {

        if (stripos($search, ';') || stripos($search, ':')) {
            $values = explode(';', $search);
            foreach ($values as $value) {
                $s = explode(':', $value);
                if (count($s) == 1) {
                    return $s[0];
                }
            }

            return null;
        }

        return $search;
    }


    protected function parserFieldsSearch(array $fields = [], array $searchFields = null)
    {
        if (!is_null($searchFields) && count($searchFields)) {
            $acceptedConditions = config('criteria.acceptedConditions', [
                '=',
                'in',
                'like'
            ]);
            $originalFields = $fields;
            $fields = [];

            foreach ($searchFields as $index => $field) {
                $field_parts = explode(':', $field);
                $temporaryIndex = array_search($field_parts[0], $originalFields);

                if (count($field_parts) == 2) {
                    if (in_array($field_parts[1], $acceptedConditions)) {
                        unset($originalFields[$temporaryIndex]);
                        $field = $field_parts[0];
                        $condition = $field_parts[1];
                        $originalFields[$field] = $condition;
                        $searchFields[$index] = $field;
                    }
                }
            }

            foreach ($originalFields as $field => $condition) {
                if (is_numeric($field)) {
                    $field = $condition;
                    $condition = "=";
                }
                if (in_array($field, $searchFields)) {
                    $fields[$field] = $condition;
                }
            }

            if (count($fields) == 0) {
                throw new \Exception(trans('criteria.fields_not_accepted', ['field' => implode(',', $searchFields)]));
            }

        }

        return $fields;
    }
}