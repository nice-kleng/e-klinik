<?php

namespace App\Providers;

use App\Models\Configuration;
use Illuminate\Support\ServiceProvider;

class ConfigServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        foreach (['pharmacy', 'billing'] as $group) {
            $this->loadConfigGroup($group);
        }
    }

    protected function loadConfigGroup(string $group): void
    {
        try {
            $configs = Configuration::where('group', $group)->get();

            foreach ($configs as $c) {
                $value = match ($c->data_type) {
                    'boolean' => filter_var($c->value, FILTER_VALIDATE_BOOLEAN),
                    'integer' => (int) $c->value,
                    'json' => json_decode($c->value, true),
                    default => $c->value,
                };

                config(["{$group}.{$c->key}" => $value]);
            }
        } catch (\Throwable $e) {
            // DB not ready yet (migrating, fresh install, etc.)
            // Fallback ke default di config/{$group}.php
        }
    }
}
