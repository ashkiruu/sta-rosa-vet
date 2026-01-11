<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MlOcrProcessing extends Model
{
    protected $table = 'ml_ocr_processing'; // Explicitly tell Laravel the table name
    protected $primaryKey = 'OCR_ID';
    public $incrementing = true;  // <--- VERY IMPORTANT
    protected $keyType = 'int';
    
    protected $fillable = [
        'User_ID', 
        'CertificateType_ID', 
        'Document_Image_Path', 
        'Extracted_Text', 
        'Parsed_Data', 
        'Confidence_Score', 
        'Address_Match_Status', 
        'Created_Date'
    ];

    public $timestamps = true; // Use Laravel's created_at/updated_at
}