<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateTemplateVersion extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'template_id',
        'version',
        'schema_json',
        'status',
        'created_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'version' => 'integer',
            'schema_json' => 'array',
        ];
    }

    /**
     * Boot model events for immutability and referential integrity.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::updating(function (CertificateTemplateVersion $version) {
            if ($version->isDirty('schema_json')) {
                $originalStatus = $version->getOriginal('status');
                if ($originalStatus !== 'draft') {
                    throw new DomainException("Versões de templates publicadas ou ativas são imutáveis e não podem ter seu schema alterado. Crie uma nova versão.");
                }

                if ($version->certificates()->exists()) {
                    throw new DomainException("Esta versão de template já foi utilizada em certificados emitidos e seu schema é estritamente imutável.");
                }
            }
        });

        static::deleting(function (CertificateTemplateVersion $version) {
            if ($version->certificates()->exists()) {
                throw new DomainException("Não é possível excluir uma versão de template vinculada a certificados emitidos.");
            }
        });
    }

    /**
     * Template that owns this version.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class, 'template_id');
    }

    /**
     * User who authored this version.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Certificates issued using this specific template version.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'template_version_id');
    }
}
