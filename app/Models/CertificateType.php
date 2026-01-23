<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CertificateType extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'certificate_types';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'CertificateType_ID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Certificate_Name',
        'Description',
    ];

    /**
     * Certificate type constants (matching service types)
     */
    const VACCINATION = 1;
    const DEWORMING = 2;
    const CHECKUP = 3;

    /**
     * Get the certificates for this type.
     */
    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class, 'CertificateType_ID', 'CertificateType_ID');
    }

    /**
     * Get vaccination certificate type
     */
    public static function vaccination(): ?self
    {
        return self::find(self::VACCINATION);
    }

    /**
     * Get deworming certificate type
     */
    public static function deworming(): ?self
    {
        return self::find(self::DEWORMING);
    }

    /**
     * Get checkup certificate type
     */
    public static function checkup(): ?self
    {
        return self::find(self::CHECKUP);
    }

    /**
     * Get certificate type ID from service category string
     */
    public static function getIdFromCategory(string $category): int
    {
        return match (strtolower($category)) {
            'vaccination' => self::VACCINATION,
            'deworming' => self::DEWORMING,
            'checkup' => self::CHECKUP,
            default => self::VACCINATION,
        };
    }
}