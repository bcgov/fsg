<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramYear extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['guid', 'start_date', 'end_date', 'claim_percent', 'status', 'comment', 'last_touch_by_user_guid'];

    public function allocations()
    {
        return $this->hasMany(Allocation::class, 'program_year_guid', 'guid')->orderBy('created_at');
    }
}
