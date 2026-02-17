<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $table = 'certificates';
    protected $primaryKey = 'Certificate_ID';

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
        'Signature_Data',
        'Status',
        'File_Path',
        'Created_By',
        'Approved_By',
        'Approved_At',
    ];

    protected $casts = [
        'Pet_DOB' => 'date',
        'Owner_Birthdate' => 'date',
        'Service_Date' => 'date',
        'Next_Service_Date' => 'date',
        'Approved_At' => 'datetime',
    ];

    const TYPE_VACCINATION = 1;
    const TYPE_DEWORMING = 2;
    const TYPE_CHECKUP = 3;

    const STATUS_DRAFT = 'draft';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'Appointment_ID', 'Appointment_ID');
    }

    public function pet(): BelongsTo
    {
        return $this->belongsTo(Pet::class, 'Pet_ID', 'Pet_ID');
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'Owner_ID', 'User_ID');
    }

    public function certificateType(): BelongsTo
    {
        return $this->belongsTo(CertificateType::class, 'CertificateType_ID', 'CertificateType_ID');
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('Status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('Status', self::STATUS_DRAFT);
    }

    public function scopeApproved($query)
    {
        return $query->where('Status', self::STATUS_APPROVED);
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('Service_Category', $category);
    }

    public function scopeVaccination($query)
    {
        return $query->where('Service_Category', 'vaccination');
    }

    public function scopeDeworming($query)
    {
        return $query->where('Service_Category', 'deworming');
    }

    public function scopeCheckup($query)
    {
        return $query->where('Service_Category', 'checkup');
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('Service_Date', [$startDate, $endDate]);
    }

    public function isDraft(): bool
    {
        return $this->Status === self::STATUS_DRAFT;
    }

    public function isApproved(): bool
    {
        return $this->Status === self::STATUS_APPROVED;
    }

    public function isVaccination(): bool
    {
        return $this->Service_Category === 'vaccination';
    }

    public function isDeworming(): bool
    {
        return $this->Service_Category === 'deworming';
    }

    public function isCheckup(): bool
    {
        return $this->Service_Category === 'checkup';
    }

    public function getFullFilePathAttribute(): ?string
    {
        if (empty($this->File_Path)) {
            return null;
        }
        return storage_path('app/public/' . $this->File_Path);
    }

    public function fileExists(): bool
    {
        $path = $this->full_file_path;
        return $path && file_exists($path);
    }

    /**
     * Generate certificate number using the highest existing number
     * to prevent duplicates after certificate deletions.
     */
    public static function generateCertificateNumber(): string
    {
        $year = date('Y');
        $prefix = "CVO-{$year}-";

        // Find the highest existing number for this year
        $lastCertificate = self::where('Certificate_Number', 'like', "{$prefix}%")
            ->orderByRaw('CAST(SUBSTRING(Certificate_Number, ' . (strlen($prefix) + 1) . ') AS UNSIGNED) DESC')
            ->first();

        if ($lastCertificate) {
            $lastNumber = (int) substr($lastCertificate->Certificate_Number, strlen($prefix));
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public static function getCertificateTypeId(string $serviceCategory): int
    {
        return match ($serviceCategory) {
            'vaccination' => self::TYPE_VACCINATION,
            'deworming' => self::TYPE_DEWORMING,
            'checkup' => self::TYPE_CHECKUP,
            default => self::TYPE_VACCINATION,
        };
    }

    protected static function booted()
    {
        static::deleting(function ($certificate) {
            if ($certificate->File_Path && file_exists($certificate->full_file_path)) {
                unlink($certificate->full_file_path);
            }
        });
    }
}