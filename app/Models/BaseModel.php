<?php

namespace App\Models;

use App\Helpers\ErrorHelpers;
use App\Helpers;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BaseModel extends Model
{
    use SoftDeletes;

    public $timestamps = true;

    protected $dates = ['deleted_at'];

    public function index(array $attributes, int $page, int $perPage, $order, $by = null)
    {
        $skip = ($page - 1) * $perPage;

        if (is_array($attributes)) {
            $this->parseArrayAttributesForRead($attributes);
        }

        if (!($count = Helpers::getCache(get_called_class(), '', 'count'))) {
            $count = $this->count();
            Helpers::putCache(get_called_class(), '', 'count', 24*60, $count);
        }

        $result = $this->getModel($attributes);

        if (is_array($order)) {
            foreach ($order as $i) {
                $result = $result->orderBy($i['order'], $i['by']);
            }
            $result = $result->skip($skip)->take($perPage)->get();
        } else {
            $result = $result->orderBy($order, $by)->skip($skip)->take($perPage)->get();
        }

        return [
            $result,
            $count
        ];
    }

    public function add(array $data, callable $guard = null, bool $alert = true)
    {
        Helpers::deleteCache(get_called_class(), '', 'count');

        $this->parseArrayAttributesForWrite($data);

        if (Helpers::countArrayMaxDepth($data) > 1) {
            app(ErrorHelpers::class)->throw(2001, 'only single record insert is allowed');
        }

        if ($guard != null) {
            if ($alert) {
                $guard($data);
            } else {
                try {
                    $guard($data);
                } catch (\Exception $e) {
                    return ;
                }
            }
        }

        $data = array_merge($data, [
            'created_at' => Carbon::now(),
            'created_by' => null
        ]);

        try {
            $data['created_by'] = \Auth::user()->id;
        } catch (\Exception $e) {
            //
        }

        $this->insert($data);
        return $this->getModel($data)->first();
    }

    public function view($id, callable $guard = null, bool $alert = true)
    {
        $model = $this->getModel($id);

        if (!$model) {
            if ($alert) {
                app(ErrorHelpers::class)->throw(3001);
            } else {
                return ;
            }
        }

        if ($guard != null) {
            if ($alert) {
                $guard($model);
            } else {
                try {
                    $guard($model);
                } catch (\Exception $e) {
                    return ;
                }
            }
        }

        return $model;
    }

    public function del($attributes, callable $guard = null, bool $alert = true): void
    {
        Helpers::deleteCache(get_called_class(), '', 'count');
        $model = $this->getModel($attributes);

        if (!$model) {
            if ($alert) {
                app(ErrorHelpers::class)->throw(3001);
            } else {
                return ;
            }
        }

        if ($guard != null) {
            if ($alert) {
                $guard($model);
            } else {
                try {
                    $guard($model);
                } catch (\Exception $e) {
                    return ;
                }
            }
        }

        $model->delete();
    }

    public function put($attributes, array $data, callable $guard = null, bool $alert = true)
    {
        $this->parseArrayAttributesForWrite($data);

        $model = $this->getModel($attributes);

        if (!$model) {
            if ($alert) {
                app(ErrorHelpers::class)->throw(3001);
            } else {
                return ;
            }
        }

        if ($guard != null) {
            if ($alert) {
                $guard($model);
            } else {
                try {
                    $guard($model);
                } catch (\Exception $e) {
                    return ;
                }
            }
        }

        $data = array_merge($data, [
            'updated_at' => Carbon::now(),
            'updated_by' => null
        ]);

        try {
            $data['updated_by'] = \Auth::user()->id;
        } catch (\Exception $e) {
            //
        }

        $model->update($data);

        $model = $this->getModel($attributes);

        if ($model instanceof BaseModel) {
            return $model;
        } else {
            return $model->get();
        }
    }

    public function getModel($attributes)
    {
        if (is_array($attributes)) {
            $this->parseArrayAttributesForRead($attributes);

            $equals = [];

            $withIns = [];

            foreach ($attributes as $k => $v) {
                if (is_array($v)) {
                    $withIns[$k] = $v;
                } else {
                    $equals[$k] = $v;
                }
            }

            $model = $this;

            if (count($equals) > 0) {
                $model = $model->where($equals);
            }

            foreach ($withIns as $k => $v) {
                $model = $model->whereIn($k, $v);
            }

            return $model;
        } elseif (is_integer($attributes) || is_string($attributes)) {
            return $this->find($attributes);
        } else {
            return null;
        }
    }

    public function listColumns()
    {
        $list = \DB::getSchemaBuilder()->getColumnListing($this->getTable());

        return array_values(array_diff($list, $this->hidden));
    }

    /** helper functions **/

    public function formatJsonQuery($arrayQuery, &$out, $parent = '')
    {
        if (is_array($arrayQuery)) {
            foreach ($arrayQuery as $key => $vale) {
                $this->formatJsonQuery(
                    $vale,
                    $out,
                    $parent != '' ? $parent.'->'.$key : $key
                );
            }
        } else {
            $out[$parent] = $arrayQuery;
        }
    }

    public function parseArrayAttributesForWrite(array &$attributes)
    {
        foreach ($attributes as $key => $value) {
            if (isset($this->casts[$key])) {
                if (is_array($value) && $this->casts[$key] == 'array') {
                    $attributes[$key] = json_encode($value);
                }
            }
        }
    }

    public function parseArrayAttributesForRead(array &$attributes)
    {
        $data = [];
        foreach ($attributes as $key => $value) {
            if (isset($this->casts[$key])) {
                if (is_array($value) && $this->casts[$key] == 'array') {
                    $this->formatJsonQuery($value, $data, $key);
                    unset($attributes[$key]);
                }
            }
        }
        $attributes = array_merge($attributes, $data);
    }
}
