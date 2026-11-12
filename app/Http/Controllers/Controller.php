<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

abstract class Controller
{
    /**
     * Foreign keys have no ON DELETE CASCADE (milestone 3.6), so MySQL refuses to delete
     * a record that still has related data. We turn that refusal into a friendly message.
     */
    protected function tryDelete(Model $model, string $route, string $label)
    {
        try {
            $model->delete();
            return redirect()->route($route)->with('success', "$label deleted.");
        } catch (QueryException $e) {
            return redirect()->route($route)->with('error', "$label cannot be deleted because it has related records. Historical records are retained for reporting.");
        }
    }
}
