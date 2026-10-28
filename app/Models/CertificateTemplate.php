<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use App\Traits\BelongsToTenant;
use DomainException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CertificateTemplate extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'description',
        'is_default',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * Boot model events for safety and integrity.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CertificateTemplate $template) {
            // Garante que o template padrão institucional da plataforma ALVORA permaneça estritamente global (company_id = null)
            if ($template->is_default && $template->name === 'Template Padrão Institucional ALVORA') {
                $template->company_id = null;
            }
        });

        static::deleting(function (CertificateTemplate $template) {
            if ($template->versions()->whereHas('certificates')->exists()) {
                throw new DomainException("Não é possível excluir um template que possui versões vinculadas a certificados emitidos.");
            }
        });
    }

    /**
     * Versions associated with this template.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(CertificateTemplateVersion::class, 'template_id');
    }

    /**
     * Get the active version for this template.
     */
    public function activeVersion(): HasOne
    {
        return $this->hasOne(CertificateTemplateVersion::class, 'template_id')
            ->ofMany(['version' => 'max'], function ($query) {
                $query->where('status', 'active');
            });
    }

    /**
     * Check if this template is global (platform-wide).
     */
    public function isGlobal(): bool
    {
        return empty($this->company_id);
    }

    /**
     * Scope to query global platform templates explicitly, bypassing tenant isolation.
     */
    public function scopeGlobal(Builder $query): Builder
    {
        return $query->withoutGlobalScope(TenantScope::class)->whereNull('company_id');
    }
}
