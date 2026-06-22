<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->writeAudit('created', null, $model->getAuditableAttributes());
        });

        static::updated(function ($model) {
            $changes = $model->getChanges();
            unset($changes['updated_at']);
            if (empty($changes)) {
                return;
            }
            $old = [];
            foreach (array_keys($changes) as $key) {
                $old[$key] = $model->getOriginal($key);
            }
            $model->writeAudit('updated', $old, $changes);
        });

        static::deleted(function ($model) {
            $event = method_exists($model, 'isForceDeleting') && $model->isForceDeleting()
                ? 'force_deleted'
                : 'deleted';
            $model->writeAudit($event, $model->getAuditableAttributes(), null);
        });
    }

    protected function getAuditableAttributes(): array
    {
        $attributes = $this->attributesToArray();
        foreach (($this->auditExclude ?? ['password', 'remember_token']) as $field) {
            unset($attributes[$field]);
        }
        return $attributes;
    }

    public function writeAudit(string $event, ?array $old, ?array $new): void
    {
        try {
            AuditLog::create([
                'user_id'        => Auth::id(),
                'event'          => $event,
                'auditable_type' => static::class,
                'auditable_id'   => $this->getKey(),
                'description'    => class_basename(static::class) . " {$event}",
                'old_values'     => $old,
                'new_values'     => $new,
                'ip_address'     => request()->ip(),
                'user_agent'     => substr((string) request()->userAgent(), 0, 500),
            ]);
        } catch (\Throwable $e) {
            // Never break the main operation because of audit failure
            report($e);
        }
    }
}
