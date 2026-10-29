<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'icon',
        'description',
        'order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Categoria pai (se for subcategoria).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Subcategorias vinculadas.
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('order')->orderBy('name');
    }

    /**
     * Cursos vinculados diretamente a esta categoria.
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'category_id');
    }

    /**
     * Total de cursos considerando a categoria raiz e todas as suas subcategorias.
     */
    public function getTotalCoursesCountAttribute(): int
    {
        $subcategoryIds = $this->subcategories->pluck('id')->all();
        $allIds = array_merge([$this->id], $subcategoryIds);

        return Course::withoutGlobalScopes()
            ->whereIn('category_id', $allIds)
            ->count();
    }

    /**
     * Verifica se é uma categoria raiz/principal.
     */
    public function isRoot(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Scope para categorias principais (sem pai).
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope para categorias ativas.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}

