<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\RestaurantTable;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    protected array $hiddenFields = ['password', 'remember_token', 'updated_at'];

    public function created(Model $model): void
    {
        $this->log('created', $model);
    }

    public function updated(Model $model): void
    {
        $changes = collect($model->getChanges())
            ->except($this->hiddenFields)
            ->mapWithKeys(fn ($value, $key) => [
                $key => ['old' => $model->getOriginal($key), 'new' => $value],
            ])
            ->all();

        if (empty($changes)) {
            return;
        }

        $this->log('updated', $model, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model);
    }

    protected function log(string $action, Model $model, array $changes = []): void
    {
        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => get_class($model),
            'auditable_id' => $model->getKey(),
            'description' => sprintf('%s %s: %s', ucfirst($action), $this->labelFor($model), $this->identifierFor($model)),
            'changes' => $changes ?: null,
        ]);
    }

    protected function labelFor(Model $model): string
    {
        return match (get_class($model)) {
            MenuCategory::class => 'Menu Category',
            MenuItem::class => 'Menu Item',
            RestaurantTable::class => 'Table',
            User::class => 'User',
            Setting::class => 'Settings',
            Order::class => 'Order',
            default => class_basename($model),
        };
    }

    protected function identifierFor(Model $model): string
    {
        if ($model instanceof Order) {
            return $model->orderNumber();
        }

        foreach (['name', 'table_number', 'resort_name'] as $field) {
            if (! empty($model->{$field})) {
                return (string) $model->{$field};
            }
        }

        return '#'.$model->getKey();
    }
}
