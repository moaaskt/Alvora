<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAttachment extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'lesson_id',
        'company_id',
        'title',
        'file_path',
        'file_type',
        'file_size_bytes',
    ];

    /**
     * The lesson this attachment belongs to.
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    /**
     * Human readable file size.
     */
    public function formattedSize(): string
    {
        if (!$this->file_size_bytes) {
            return '';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($this->file_size_bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
