<?php

namespace App\Models;

/** Guard for database views: they are derived data and must never be written to. */
trait ReadOnlyView
{
    protected static function bootReadOnlyView(): void
    {
        foreach (['creating', 'updating', 'deleting'] as $event) {
            static::$event(fn () => throw new \LogicException('Database views are read-only.'));
        }
    }
}
