<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $table = 'expenses';
    protected $primaryKey = 'expense_id';
    public $timestamps = false;
    protected $fillable = ['farm_id', 'user_id', 'expense_category', 'description', 'expense_date', 'amount'];

    /** Suggested categories (expense_category is free text in the schema). */
    public const SUGGESTED = ['Seeds', 'Land Preparation', 'Fertilizer', 'Pesticide', 'Labor', 'Harvesting Labor', 'Fuel', 'Irrigation Fee', 'Equipment Repair', 'Transportation'];

    protected function casts(): array
    {
        return ['expense_date' => 'date'];
    }

    public function farm() { return $this->belongsTo(Farm::class, 'farm_id', 'farm_id'); }
    public function user() { return $this->belongsTo(User::class, 'user_id', 'user_id'); }

    /**
     * Prevents redundant spellings ("fertilizer" vs "Fertilizer "):
     * reuse the existing category's spelling when it matches case-insensitively.
     */
    public static function canonicalCategory(string $input): string
    {
        $input = trim(preg_replace('/\s+/', ' ', $input));
        $existing = static::query()->whereRaw('LOWER(expense_category) = ?', [mb_strtolower($input)])->value('expense_category');
        if ($existing) return $existing;
        foreach (self::SUGGESTED as $s) {
            if (mb_strtolower($s) === mb_strtolower($input)) return $s;
        }
        return mb_convert_case($input, MB_CASE_TITLE);
    }

    public static function categories(): array
    {
        return static::query()->distinct()->pluck('expense_category')
            ->merge(self::SUGGESTED)->unique()->sort()->values()->all();
    }
}
