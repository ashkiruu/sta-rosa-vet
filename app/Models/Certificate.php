<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'certificates';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'Certificate_ID';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Certificate_Number',
        'Appointment_ID',
        'Pet_ID',
        'Owner_ID',
        'CertificateType_ID',
        'Service_Type',
        'Service_Category',
        'Pet_Name',
        'Animal_Type',
        'Pet_Gender',
        'Pet_Age',
        'Pet_Breed',
        'Pet_Color',
        'Pet_DOB',
        'Owner_Name',
        'Owner_Address',
        'Owner_Phone',
        'Civil_Status',
        'Years_Of_Residency',
        'Owner_Birthdate',
        'Service_Date',
        'Next_Service_Date',
        'Vaccine_Type',
        'Vaccine_Used',
        'Lot_Number',
        'Medicine_Used',
        'Dosage',
        'Findings',
        'Recommendations',
        'Vet_Name',
        'License_Number',
        'PTR_Number',
        'Status',
        'File_Path',
        'Created_By',
        'Approved_By',
        'Approved_At',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'Pet_DOB' => 'date',
        'Owner_Birthdate' => 'date',
        'Service_Date' => 'date',
        'Next_Service_Date' => 'date',
        'Approved_At' => 'datetime',
    ];

    /**
     * Certificate type constants (matching service types)
     */
    const TYPE_VACCINATION = 1;
    const TYPE_DEWORMING = 2;
    const TYPE_CHECKUP = 3;

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the appointment for this certificate.
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'Appointment_ID', 'Appointment_ID');
    }

    /**
     * Get the pet for this certificate.
     */
    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'Pet_ID', 'Pet_ID');
    }

    /**
     * Get the owner (user) for this certificate.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Owner_ID', 'User_ID');
    }

    /**
     * Get the certificate type.
     */
    public function certificateType(): BelongsTo
    {
        return $this->belongsTo(CertificateType::class, 'CertificateType_ID', 'CertificateType_ID');
    }

    /**
     * Scope to filter by status
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('Status', $status);
    }

    /**
     * Scope for draft certificates
     */
    public function scopeDraft($query)
    {
        return $query->where('Status', self::STATUS_DRAFT);
    }

    /**
     * Scope for approved certificates
     */
    public function scopeApproved($query)
    {
        return $query->where('Status', self::STATUS_APPROVED);
    }

    /**
     * Scope to filter by service category
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('Service_Category', $category);
    }

    /**
     * Scope for vaccination certificates
     */
    public function scopeVaccination($query)
    {
        return $query->where('Service_Category', 'vaccination');
    }

    /**
     * Scope for deworming certificates
     */
    public function scopeDeworming($query)
    {
        return $query->where('Service_Category', 'deworming');
    }

    /**
     * Scope for checkup certificates
     */
    public function scopeCheckup($query)
    {
        return $query->where('Service_Category', 'checkup');
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('Service_Date', [$startDate, $endDate]);
    }

    /**
     * Check if certificate is a draft
     */
    public function isDraft(): bool
    {
        return $this->Status === self::STATUS_DRAFT;
    }

    /**
     * Check if certificate is approved
     */
    public function isApproved(): bool
    {
        return $this->Status === self::STATUS_APPROVED;
    }

    /**
     * Check if this is a vaccination certificate
     */
    public function isVaccination(): bool
    {
        return $this->Service_Category === 'vaccination';
    }

    /**
     * Check if this is a deworming certificate
     */
    public function isDeworming(): bool
    {
        return $this->Service_Category === 'deworming';
    }

    /**
     * Check if this is a checkup certificate
     */
    public function isCheckup(): bool
    {
        return $this->Service_Category === 'checkup';
    }

    /**
     * Get the full file path
     */
    public function getFullFilePathAttribute(): ?string
    {
        if (empty($this->File_Path)) {
            return null;
        }
        return storage_path('app/public/' . $this->File_Path);
    }

    /**
     * Check if the certificate file exists
     */
    public function fileExists(): bool
    {
        $path = $this->full_file_path;
        return $path && file_exists($path);
    }

    /**
     * Generate certificate number
     */
    public static function generateCertificateNumber(): string
    {
        $year = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return "CVO-{$year}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Get certificate type ID from service category
     */
    public static function getCertificateTypeId(string $serviceCategory): int
    {
        return match ($serviceCategory) {
            'vaccination' => self::TYPE_VACCINATION,
            'deworming' => self::TYPE_DEWORMING,
            'checkup' => self::TYPE_CHECKUP,
            default => self::TYPE_VACCINATION,
        };
    }

    /**
     * Delete the associated file when the certificate is deleted
     */
    protected static function booted()
    {
        static::deleting(function ($certificate) {
            if ($certificate->File_Path && file_exists($certificate->full_file_path)) {
                unlink($certificate->full_file_path);
            }
        });
    }
}