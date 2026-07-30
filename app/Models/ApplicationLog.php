<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'actor_id',
        'old_status',
        'new_status',
        'remarks',
        'created_at',
    ];

    protected $casts = [
        'old_status' => ProjectStatus::class,
        'new_status' => ProjectStatus::class,
        'created_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
