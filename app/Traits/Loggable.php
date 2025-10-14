<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Loggable
{
    protected static function bootLoggable()
    {
        static::created(function ($model) {
            self::logAction($model, 'created');
        });

        static::updated(function ($model) {
            self::logAction($model, 'updated');
        });

        static::deleted(function ($model) {
            self::logAction($model, 'deleted');
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                self::logAction($model, 'restored');
            });
        }
    }

    protected static function logAction($model, $action)
    {
        if (!Auth::check()) {
            return;
        }

        $oldData = null;
        $newData = null;
        $description = null;

        switch ($action) {
            case 'created':
                $newData = $model->getAttributes();
                $description = "Novo registro criado em " . class_basename($model);
                break;
            
            case 'updated':
                $oldData = array_intersect_key($model->getOriginal(), $model->getDirty());
                $newData = $model->getDirty();
                $description = "Registro atualizado em " . class_basename($model);
                break;
            
            case 'deleted':
                $oldData = $model->getOriginal();
                $description = "Registro excluído em " . class_basename($model);
                break;
            
            case 'restored':
                $description = "Registro restaurado em " . class_basename($model);
                break;
        }

        \App\Models\SystemLog::create([
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_data' => $oldData,
            'new_data' => $newData,
            'description' => $description,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'user_id' => Auth::id(),
        ]);
    }
}