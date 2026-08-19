<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'created', array_keys($model->getAttributes()));
    }

    public function updated(Model $model): void
    {
        $this->record($model, 'updated', array_keys($model->getChanges()));
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', []);
    }

    private function record(Model $model, string $event, array $changedFields): void
    {
        AuditLog::record(
            strtolower(class_basename($model)).'.'.$event,
            $model,
            ['changed_fields' => array_values(array_diff($changedFields, ['created_at', 'updated_at']))],
        );
    }
}