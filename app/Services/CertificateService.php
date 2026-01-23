<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Certificate;
use App\Models\CertificateType;
use App\Models\Pet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CertificateService
{
    /**
     * Get PDF storage directory
     */
    private static function getPdfDirectory()
    {
        $dir = storage_path('app/public/certificates');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Generate certificate number
     */
    public static function generateCertificateNumber()
    {
        return Certificate::generateCertificateNumber();
    }

    /**
     * Determine service category from service type name
     */
    public static function getServiceCategory($serviceType)
    {
        $serviceType = strtolower($serviceType);
        
        if (strpos($serviceType, 'vaccination') !== false || strpos($serviceType, 'vaccine') !== false) {
            return 'vaccination';
        } elseif (strpos($serviceType, 'deworming') !== false) {
            return 'deworming';
        } elseif (strpos($serviceType, 'checkup') !== false || strpos($serviceType, 'check-up') !== false) {
            return 'checkup';
        }
        
        return 'other';
    }

    /**
     * Create a new certificate
     */
    public static function createCertificate($data)
    {
        $certificateNumber = self::generateCertificateNumber();
        
        // Determine service category
        $serviceCategory = self::getServiceCategory($data['service_type'] ?? '');
        
        // Get certificate type ID based on service category
        $certificateTypeId = CertificateType::getIdFromCategory($serviceCategory);
        
        // Process vaccine data for vaccination service
        $vaccineUsed = null;
        $lotNumber = null;
        $vaccineType = null;
        
        if ($serviceCategory === 'vaccination') {
            $vaccineType = $data['vaccine_type'] ?? null;
            
            if ($vaccineType === 'anti-rabies') {
                $vaccineUsed = $data['vaccine_name_rabies'] ?? $data['vaccine_used'] ?? 'Anti-Rabies Vaccine';
                $lotNumber = $data['lot_number'] ?? $data['lot_number_final'] ?? null;
            } elseif ($vaccineType === 'other') {
                $vaccineUsed = $data['vaccine_name_other'] ?? $data['vaccine_used'] ?? null;
                $lotNumber = $data['lot_number_other'] ?? $data['lot_number_final'] ?? null;
            } else {
                $vaccineUsed = $data['vaccine_used'] ?? null;
                $lotNumber = $data['lot_number'] ?? null;
            }
        }

        // Get appointment to link Pet_ID and Owner_ID
        $appointment = null;
        $petId = null;
        $ownerId = null;
        
        if (!empty($data['appointment_id'])) {
            $appointment = Appointment::with(['pet', 'user'])->find($data['appointment_id']);
            if ($appointment) {
                $petId = $appointment->Pet_ID;
                $ownerId = $appointment->User_ID;
            }
        }
        
        $certificate = Certificate::create([
            'Certificate_Number' => $certificateNumber,
            'Appointment_ID' => $data['appointment_id'] ?? null,
            'Pet_ID' => $petId,
            'Owner_ID' => $ownerId,
            'CertificateType_ID' => $certificateTypeId,
            
            // Service Information
            'Service_Type' => $data['service_type'],
            'Service_Category' => $serviceCategory,
            
            // Pet Information
            'Pet_Name' => $data['pet_name'],
            'Animal_Type' => $data['animal_type'],
            'Pet_Gender' => $data['pet_gender'],
            'Pet_Age' => $data['pet_age'],
            'Pet_Breed' => $data['pet_breed'],
            'Pet_Color' => $data['pet_color'],
            'Pet_DOB' => $data['pet_dob'] ?? null,
            
            // Owner Information
            'Owner_Name' => $data['owner_name'],
            'Owner_Address' => $data['owner_address'],
            'Owner_Phone' => $data['owner_phone'],
            'Civil_Status' => $data['civil_status'] ?? null,
            'Years_Of_Residency' => $data['years_of_residency'] ?? null,
            'Owner_Birthdate' => $data['owner_birthdate'] ?? null,
            
            // Service Dates
            'Service_Date' => $data['service_date'] ?? $data['vaccination_date'] ?? null,
            'Next_Service_Date' => $data['next_service_date'] ?? $data['next_vaccination_date'] ?? null,
            
            // Vaccination-specific
            'Vaccine_Type' => $vaccineType,
            'Vaccine_Used' => $vaccineUsed,
            'Lot_Number' => $lotNumber,
            
            // Deworming-specific
            'Medicine_Used' => $data['medicine_used'] ?? null,
            'Dosage' => $data['dosage'] ?? null,
            
            // Checkup-specific
            'Findings' => $data['findings'] ?? null,
            'Recommendations' => $data['recommendations'] ?? null,
            
            // Veterinarian Details
            'Vet_Name' => $data['veterinarian_name'],
            'License_Number' => $data['license_number'],
            'PTR_Number' => $data['ptr_number'],
            
            // System Fields
            'Status' => Certificate::STATUS_DRAFT,
            'Created_By' => $data['created_by'] ?? 'Admin',
        ]);
        
        return self::certificateToArray($certificate);
    }

    /**
     * Update certificate
     */
    public static function updateCertificate($certificateId, $data)
    {
        $certificate = Certificate::find($certificateId);
        
        if (!$certificate) {
            return null;
        }
        
        // Determine service category
        $serviceType = $data['service_type'] ?? $certificate->Service_Type;
        $serviceCategory = self::getServiceCategory($serviceType);
        
        // Process vaccine data for vaccination service
        if ($serviceCategory === 'vaccination') {
            $vaccineType = $data['vaccine_type'] ?? $certificate->Vaccine_Type;
            
            if ($vaccineType === 'anti-rabies') {
                $data['Vaccine_Used'] = $data['vaccine_name_rabies'] ?? $data['vaccine_used'] ?? $certificate->Vaccine_Used ?? 'Anti-Rabies Vaccine';
                $data['Lot_Number'] = $data['lot_number'] ?? $data['lot_number_final'] ?? $certificate->Lot_Number;
            } elseif ($vaccineType === 'other') {
                $data['Vaccine_Used'] = $data['vaccine_name_other'] ?? $data['vaccine_used'] ?? $certificate->Vaccine_Used;
                $data['Lot_Number'] = $data['lot_number_other'] ?? $data['lot_number_final'] ?? $certificate->Lot_Number;
            }
            
            $data['Vaccine_Type'] = $vaccineType;
        }
        
        // Map fields from form to database columns
        $updateData = [
            'Service_Type' => $data['service_type'] ?? $certificate->Service_Type,
            'Service_Category' => $serviceCategory,
            'CertificateType_ID' => CertificateType::getIdFromCategory($serviceCategory),
            
            // Pet Information
            'Pet_Name' => $data['pet_name'] ?? $certificate->Pet_Name,
            'Animal_Type' => $data['animal_type'] ?? $certificate->Animal_Type,
            'Pet_Gender' => $data['pet_gender'] ?? $certificate->Pet_Gender,
            'Pet_Age' => $data['pet_age'] ?? $certificate->Pet_Age,
            'Pet_Breed' => $data['pet_breed'] ?? $certificate->Pet_Breed,
            'Pet_Color' => $data['pet_color'] ?? $certificate->Pet_Color,
            'Pet_DOB' => $data['pet_dob'] ?? $certificate->Pet_DOB,
            
            // Owner Information
            'Owner_Name' => $data['owner_name'] ?? $certificate->Owner_Name,
            'Owner_Address' => $data['owner_address'] ?? $certificate->Owner_Address,
            'Owner_Phone' => $data['owner_phone'] ?? $certificate->Owner_Phone,
            'Civil_Status' => $data['civil_status'] ?? $certificate->Civil_Status,
            'Years_Of_Residency' => $data['years_of_residency'] ?? $certificate->Years_Of_Residency,
            'Owner_Birthdate' => $data['owner_birthdate'] ?? $certificate->Owner_Birthdate,
            
            // Service Dates
            'Service_Date' => $data['service_date'] ?? $data['vaccination_date'] ?? $certificate->Service_Date,
            'Next_Service_Date' => $data['next_service_date'] ?? $data['next_vaccination_date'] ?? $certificate->Next_Service_Date,
            
            // Vaccination-specific
            'Vaccine_Type' => $data['Vaccine_Type'] ?? $data['vaccine_type'] ?? $certificate->Vaccine_Type,
            'Vaccine_Used' => $data['Vaccine_Used'] ?? $data['vaccine_used'] ?? $certificate->Vaccine_Used,
            'Lot_Number' => $data['Lot_Number'] ?? $data['lot_number'] ?? $certificate->Lot_Number,
            
            // Deworming-specific
            'Medicine_Used' => $data['medicine_used'] ?? $certificate->Medicine_Used,
            'Dosage' => $data['dosage'] ?? $certificate->Dosage,
            
            // Checkup-specific
            'Findings' => $data['findings'] ?? $certificate->Findings,
            'Recommendations' => $data['recommendations'] ?? $certificate->Recommendations,
            
            // Veterinarian Details
            'Vet_Name' => $data['veterinarian_name'] ?? $certificate->Vet_Name,
            'License_Number' => $data['license_number'] ?? $certificate->License_Number,
            'PTR_Number' => $data['ptr_number'] ?? $certificate->PTR_Number,
        ];
        
        $certificate->update($updateData);
        
        return self::certificateToArray($certificate->fresh());
    }

    /**
     * Approve certificate and generate PDF
     */
    public static function approveCertificate($certificateId, $approvedBy = 'Admin')
    {
        $certificate = Certificate::find($certificateId);
        
        if (!$certificate) {
            return null;
        }
        
        // Generate PDF
        $pdfPath = self::generatePdf(self::certificateToArray($certificate));
        
        // Update certificate status
        $certificate->update([
            'Status' => Certificate::STATUS_APPROVED,
            'Approved_At' => now(),
            'Approved_By' => $approvedBy,
            'File_Path' => $pdfPath,
        ]);
        
        // Sync appointment status to 'Completed'
        self::syncAppointmentStatus($certificate->Appointment_ID);
        
        return self::certificateToArray($certificate->fresh());
    }

    /**
     * Sync appointment status to 'Completed' when certificate is approved
     */
    private static function syncAppointmentStatus($appointmentId)
    {
        if (!$appointmentId) {
            return false;
        }
        
        $appointment = Appointment::find($appointmentId);
        if ($appointment && $appointment->Status !== 'Completed') {
            $appointment->Status = 'Completed';
            $appointment->save();
            
            Log::info("Certificate approval synced appointment {$appointmentId} status to Completed");
            return true;
        }
        
        return false;
    }

    /**
     * Get certificate by ID
     */
    public static function getCertificate($certificateId)
    {
        $certificate = Certificate::find($certificateId);
        
        if (!$certificate) {
            return null;
        }
        
        return self::certificateToArray($certificate);
    }

    /**
     * Get certificate by appointment ID
     */
    public static function getCertificateByAppointment($appointmentId)
    {
        $certificate = Certificate::where('Appointment_ID', $appointmentId)->first();
        
        if (!$certificate) {
            return null;
        }
        
        return self::certificateToArray($certificate);
    }

    /**
     * Get all certificates for a pet owner (by user_id)
     */
    public static function getCertificatesByOwner($userId)
    {
        $certificates = Certificate::where('Owner_ID', $userId)
            ->approved()
            ->orderBy('created_at', 'desc')
            ->get();
        
        return $certificates->map(fn($cert) => self::certificateToArray($cert))->toArray();
    }

    /**
     * Delete certificate
     */
    public static function deleteCertificate($certificateId)
    {
        $certificate = Certificate::find($certificateId);
        
        if (!$certificate) {
            return false;
        }
        
        // The model's booted method will handle file deletion
        $certificate->delete();
        
        return true;
    }

    /**
     * Get all certificates with optional status filter
     */
    public static function getAllCertificates($status = null)
    {
        $query = Certificate::orderBy('created_at', 'desc');
        
        if ($status) {
            $query->status($status);
        }
        
        return $query->get()->map(fn($cert) => self::certificateToArray($cert))->toArray();
    }

    /**
     * Convert Certificate model to array format for backward compatibility
     */
    private static function certificateToArray(Certificate $certificate): array
    {
        return [
            'id' => $certificate->Certificate_ID,
            'certificate_number' => $certificate->Certificate_Number,
            'appointment_id' => $certificate->Appointment_ID,
            
            // Service Information
            'service_type' => $certificate->Service_Type,
            'service_category' => $certificate->Service_Category,
            
            // Pet Information
            'pet_name' => $certificate->Pet_Name,
            'animal_type' => $certificate->Animal_Type,
            'pet_gender' => $certificate->Pet_Gender,
            'pet_age' => $certificate->Pet_Age,
            'pet_breed' => $certificate->Pet_Breed,
            'pet_color' => $certificate->Pet_Color,
            'pet_dob' => $certificate->Pet_DOB?->format('Y-m-d'),
            
            // Owner Information
            'owner_name' => $certificate->Owner_Name,
            'owner_address' => $certificate->Owner_Address,
            'owner_phone' => $certificate->Owner_Phone,
            'civil_status' => $certificate->Civil_Status,
            'years_of_residency' => $certificate->Years_Of_Residency,
            'owner_birthdate' => $certificate->Owner_Birthdate?->format('Y-m-d'),
            
            // Service Dates
            'service_date' => $certificate->Service_Date?->format('Y-m-d'),
            'next_service_date' => $certificate->Next_Service_Date?->format('Y-m-d'),
            'vaccination_date' => $certificate->Service_Date?->format('Y-m-d'), // Legacy compatibility
            'next_vaccination_date' => $certificate->Next_Service_Date?->format('Y-m-d'), // Legacy compatibility
            
            // Vaccination-specific
            'vaccine_type' => $certificate->Vaccine_Type,
            'vaccine_used' => $certificate->Vaccine_Used,
            'lot_number' => $certificate->Lot_Number,
            
            // Deworming-specific
            'medicine_used' => $certificate->Medicine_Used,
            'dosage' => $certificate->Dosage,
            
            // Checkup-specific
            'findings' => $certificate->Findings,
            'recommendations' => $certificate->Recommendations,
            
            // Veterinarian Details
            'veterinarian_name' => $certificate->Vet_Name,
            'license_number' => $certificate->License_Number,
            'ptr_number' => $certificate->PTR_Number,
            
            // System Fields
            'status' => $certificate->Status,
            'pdf_path' => $certificate->File_Path,
            'created_at' => $certificate->created_at?->format('Y-m-d H:i:s'),
            'created_by' => $certificate->Created_By,
            'approved_at' => $certificate->Approved_At?->format('Y-m-d H:i:s'),
            'approved_by' => $certificate->Approved_By,
        ];
    }

    /**
     * Generate PDF certificate using HTML
     */
    public static function generatePdf($certificate)
    {
        $serviceCategory = $certificate['service_category'] ?? self::getServiceCategory($certificate['service_type'] ?? '');
        
        switch ($serviceCategory) {
            case 'vaccination':
                $html = self::generateVaccinationCertificateHtml($certificate);
                break;
            case 'deworming':
                $html = self::generateDewormingCertificateHtml($certificate);
                break;
            case 'checkup':
                $html = self::generateCheckupCertificateHtml($certificate);
                break;
            default:
                $html = self::generateGenericCertificateHtml($certificate);
        }
        
        $filename = 'certificates/certificate_' . $certificate['id'] . '.html';
        $fullPath = storage_path('app/public/' . $filename);
        
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($fullPath, $html);
        
        return $filename;
    }

    /**
     * Generate Vaccination certificate HTML content
     */
    public static function generateVaccinationCertificateHtml($certificate)
    {
        $certificateNumber = $certificate['certificate_number'];
        $petName = htmlspecialchars($certificate['pet_name']);
        $animalType = htmlspecialchars($certificate['animal_type']);
        $petGender = htmlspecialchars($certificate['pet_gender']);
        $petAge = htmlspecialchars($certificate['pet_age']);
        $petBreed = htmlspecialchars($certificate['pet_breed']);
        $petColor = htmlspecialchars($certificate['pet_color']);
        $petDob = $certificate['pet_dob'] ? date('F d, Y', strtotime($certificate['pet_dob'])) : '';
        
        $ownerName = htmlspecialchars($certificate['owner_name']);
        $ownerAddress = htmlspecialchars($certificate['owner_address']);
        $ownerPhone = htmlspecialchars($certificate['owner_phone']);
        
        $vaccinationDate = ($certificate['service_date'] ?? $certificate['vaccination_date']) 
            ? date('M d, Y', strtotime($certificate['service_date'] ?? $certificate['vaccination_date'])) 
            : '';
        $lotNumber = htmlspecialchars($certificate['lot_number'] ?? '');
        $nextVaccinationDate = ($certificate['next_service_date'] ?? $certificate['next_vaccination_date']) 
            ? date('M d, Y', strtotime($certificate['next_service_date'] ?? $certificate['next_vaccination_date'])) 
            : '';
        $vaccineUsed = htmlspecialchars($certificate['vaccine_used'] ?? '');
        
        $veterinarianName = htmlspecialchars($certificate['veterinarian_name']);
        $licenseNumber = htmlspecialchars($certificate['license_number']);
        $ptrNumber = htmlspecialchars($certificate['ptr_number']);
        
        $issuedDate = date('F d, Y');
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Vaccination Card - {$certificateNumber}</title>
    <style>
        @media print {
            body { margin: 0; padding: 10px; }
            .no-print { display: none !important; }
            .card-container { box-shadow: none !important; }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .no-print { text-align: center; margin-bottom: 20px; }
        .no-print button { background: #333; color: white; border: none; padding: 12px 30px; font-size: 16px; cursor: pointer; border-radius: 5px; margin: 0 10px; }
        .no-print button:hover { background: #555; }
        .card-container { max-width: 700px; margin: 0 auto; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: relative; }
        .card { padding: 30px 40px; position: relative; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 200px; opacity: 0.08; z-index: 0; font-size: 150px; text-align: center; line-height: 200px; }
        .content { position: relative; z-index: 1; }
        .header { text-align: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #333; }
        .header h1 { font-size: 28px; font-weight: bold; color: #333; letter-spacing: 1px; }
        .cert-number { text-align: right; font-size: 11px; color: #666; margin-bottom: 15px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { border: 1px solid #333; padding: 8px 12px; vertical-align: middle; }
        .info-table .label { font-weight: bold; color: #333; font-size: 12px; background: #fafafa; white-space: nowrap; }
        .info-table .value { font-size: 13px; color: #333; }
        .info-table .label-highlight { font-weight: bold; color: #b8860b; font-size: 12px; background: #fafafa; white-space: nowrap; }
        .vacc-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .vacc-table th { border: 1px solid #333; padding: 10px 8px; font-size: 11px; font-weight: bold; text-align: center; background: #fafafa; color: #333; }
        .vacc-table td { border: 1px solid #333; padding: 10px 8px; font-size: 12px; text-align: center; }
        .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #ccc; text-align: center; font-size: 10px; color: #666; }
        .program-title { font-size: 11px; font-weight: bold; color: #333; text-align: center; margin-top: 15px; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ Print / Save as PDF</button>
        <button onclick="window.close()">✕ Close</button>
    </div>
    <div class="card-container">
        <div class="card">
            <div class="watermark">🐾</div>
            <div class="content">
                <div class="header"><h1>Pet Vaccination Card</h1></div>
                <div class="cert-number">Certificate No: {$certificateNumber}</div>
                <table class="info-table">
                    <tr><td class="label">Pet's Name:</td><td class="value" colspan="3" style="font-weight: bold;">{$petName}</td></tr>
                    <tr><td class="label">Type of Animal:</td><td class="value">{$animalType}</td><td class="label-highlight">Sex:</td><td class="value">{$petGender}</td></tr>
                    <tr><td class="label">Age:</td><td class="value">{$petAge}</td><td class="label-highlight">Breed:</td><td class="value">{$petBreed}</td></tr>
                    <tr><td class="label">Color:</td><td class="value">{$petColor}</td><td class="label-highlight">Date of Birth:</td><td class="value">{$petDob}</td></tr>
                    <tr><td class="label">Owner's Name:</td><td class="value" colspan="3">{$ownerName}</td></tr>
                    <tr><td class="label">Address:</td><td class="value" colspan="3">{$ownerAddress}</td></tr>
                    <tr><td class="label">Cellphone/Telephone Number:</td><td class="value" colspan="3">{$ownerPhone}</td></tr>
                </table>
                <table class="vacc-table">
                    <thead><tr><th>Date of<br>Vaccination</th><th>Vaccine<br>Used</th><th>Lot No./<br>Batch No.</th><th>Date of Next<br>Vaccination</th><th>Veterinarian<br>Lic No. PTR</th></tr></thead>
                    <tbody><tr><td>{$vaccinationDate}</td><td>{$vaccineUsed}</td><td>{$lotNumber}</td><td>{$nextVaccinationDate}</td><td style="font-size: 10px;">{$veterinarianName}<br>Lic: {$licenseNumber}<br>PTR: {$ptrNumber}</td></tr></tbody>
                </table>
                <div class="footer"><p>Issued on: {$issuedDate} | City Veterinary Office</p></div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate Deworming certificate HTML content
     */
    public static function generateDewormingCertificateHtml($certificate)
    {
        $certificateNumber = $certificate['certificate_number'];
        $petName = htmlspecialchars($certificate['pet_name']);
        $animalType = htmlspecialchars($certificate['animal_type']);
        $petGender = htmlspecialchars($certificate['pet_gender']);
        $petAge = htmlspecialchars($certificate['pet_age']);
        $petBreed = htmlspecialchars($certificate['pet_breed']);
        $petColor = htmlspecialchars($certificate['pet_color']);
        $petDob = $certificate['pet_dob'] ? date('F d, Y', strtotime($certificate['pet_dob'])) : '';
        
        $ownerName = htmlspecialchars($certificate['owner_name']);
        $ownerAddress = htmlspecialchars($certificate['owner_address']);
        $ownerPhone = htmlspecialchars($certificate['owner_phone']);
        
        $serviceDate = ($certificate['service_date'] ?? $certificate['vaccination_date']) 
            ? date('M d, Y', strtotime($certificate['service_date'] ?? $certificate['vaccination_date'])) 
            : '';
        $nextServiceDate = ($certificate['next_service_date'] ?? $certificate['next_vaccination_date']) 
            ? date('M d, Y', strtotime($certificate['next_service_date'] ?? $certificate['next_vaccination_date'])) 
            : '';
        $medicineUsed = htmlspecialchars($certificate['medicine_used'] ?? $certificate['vaccine_used'] ?? '');
        $dosage = htmlspecialchars($certificate['dosage'] ?? '');
        
        $veterinarianName = htmlspecialchars($certificate['veterinarian_name']);
        $licenseNumber = htmlspecialchars($certificate['license_number']);
        $ptrNumber = htmlspecialchars($certificate['ptr_number']);
        
        $issuedDate = date('F d, Y');
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Deworming Card - {$certificateNumber}</title>
    <style>
        @media print { body { margin: 0; padding: 10px; } .no-print { display: none !important; } .card-container { box-shadow: none !important; } }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .no-print { text-align: center; margin-bottom: 20px; }
        .no-print button { background: #059669; color: white; border: none; padding: 12px 30px; font-size: 16px; cursor: pointer; border-radius: 5px; margin: 0 10px; }
        .no-print button:hover { background: #047857; }
        .card-container { max-width: 700px; margin: 0 auto; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: relative; }
        .card { padding: 30px 40px; position: relative; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 200px; opacity: 0.08; z-index: 0; font-size: 150px; text-align: center; line-height: 200px; }
        .content { position: relative; z-index: 1; }
        .header { text-align: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #059669; }
        .header h1 { font-size: 28px; font-weight: bold; color: #059669; letter-spacing: 1px; }
        .cert-number { text-align: right; font-size: 11px; color: #666; margin-bottom: 15px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { border: 1px solid #333; padding: 8px 12px; vertical-align: middle; }
        .info-table .label { font-weight: bold; color: #333; font-size: 12px; background: #f0fdf4; white-space: nowrap; }
        .info-table .value { font-size: 13px; color: #333; }
        .info-table .label-highlight { font-weight: bold; color: #059669; font-size: 12px; background: #f0fdf4; white-space: nowrap; }
        .record-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .record-table th { border: 1px solid #333; padding: 10px 8px; font-size: 11px; font-weight: bold; text-align: center; background: #f0fdf4; color: #333; }
        .record-table td { border: 1px solid #333; padding: 10px 8px; font-size: 12px; text-align: center; }
        .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #ccc; text-align: center; font-size: 10px; color: #666; }
        .program-title { font-size: 11px; font-weight: bold; color: #059669; text-align: center; margin-top: 15px; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ Print / Save as PDF</button>
        <button onclick="window.close()">✕ Close</button>
    </div>
    <div class="card-container">
        <div class="card">
            <div class="watermark">💊</div>
            <div class="content">
                <div class="header"><h1>Pet Deworming Card</h1></div>
                <div class="cert-number">Certificate No: {$certificateNumber}</div>
                <table class="info-table">
                    <tr><td class="label">Pet's Name:</td><td class="value" colspan="3" style="font-weight: bold;">{$petName}</td></tr>
                    <tr><td class="label">Type of Animal:</td><td class="value">{$animalType}</td><td class="label-highlight">Sex:</td><td class="value">{$petGender}</td></tr>
                    <tr><td class="label">Age:</td><td class="value">{$petAge}</td><td class="label-highlight">Breed:</td><td class="value">{$petBreed}</td></tr>
                    <tr><td class="label">Color:</td><td class="value">{$petColor}</td><td class="label-highlight">Date of Birth:</td><td class="value">{$petDob}</td></tr>
                    <tr><td class="label">Owner's Name:</td><td class="value" colspan="3">{$ownerName}</td></tr>
                    <tr><td class="label">Address:</td><td class="value" colspan="3">{$ownerAddress}</td></tr>
                    <tr><td class="label">Cellphone/Telephone Number:</td><td class="value" colspan="3">{$ownerPhone}</td></tr>
                </table>
                <table class="record-table">
                    <thead><tr><th>Date of<br>Deworming</th><th>Medicine<br>Used</th><th>Dosage</th><th>Date of Next<br>Deworming</th><th>Veterinarian<br>Lic No. PTR</th></tr></thead>
                    <tbody><tr><td>{$serviceDate}</td><td>{$medicineUsed}</td><td>{$dosage}</td><td>{$nextServiceDate}</td><td style="font-size: 10px;">{$veterinarianName}<br>Lic: {$licenseNumber}<br>PTR: {$ptrNumber}</td></tr></tbody>
                </table>
                <div class="program-title">PET HEALTH AND WELLNESS PROGRAM</div>
                <div class="footer"><p>Issued on: {$issuedDate} | City Veterinary Office</p></div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate Checkup certificate HTML content
     */
    public static function generateCheckupCertificateHtml($certificate)
    {
        $certificateNumber = $certificate['certificate_number'];
        $petName = htmlspecialchars($certificate['pet_name']);
        $animalType = htmlspecialchars($certificate['animal_type']);
        $petGender = htmlspecialchars($certificate['pet_gender']);
        $petAge = htmlspecialchars($certificate['pet_age']);
        $petBreed = htmlspecialchars($certificate['pet_breed']);
        $petColor = htmlspecialchars($certificate['pet_color']);
        $petDob = $certificate['pet_dob'] ? date('F d, Y', strtotime($certificate['pet_dob'])) : '';
        
        $ownerName = htmlspecialchars($certificate['owner_name']);
        $ownerAddress = htmlspecialchars($certificate['owner_address']);
        $ownerPhone = htmlspecialchars($certificate['owner_phone']);
        
        $serviceDate = ($certificate['service_date'] ?? $certificate['vaccination_date']) 
            ? date('M d, Y', strtotime($certificate['service_date'] ?? $certificate['vaccination_date'])) 
            : '';
        $nextServiceDate = ($certificate['next_service_date'] ?? $certificate['next_vaccination_date']) 
            ? date('M d, Y', strtotime($certificate['next_service_date'] ?? $certificate['next_vaccination_date'])) 
            : '';
        $findings = htmlspecialchars($certificate['findings'] ?? 'No significant findings. Pet is in good health.');
        $recommendations = htmlspecialchars($certificate['recommendations'] ?? '');
        
        $veterinarianName = htmlspecialchars($certificate['veterinarian_name']);
        $licenseNumber = htmlspecialchars($certificate['license_number']);
        $ptrNumber = htmlspecialchars($certificate['ptr_number']);
        
        $issuedDate = date('F d, Y');
        
        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pet Health Checkup Card - {$certificateNumber}</title>
    <style>
        @media print { body { margin: 0; padding: 10px; } .no-print { display: none !important; } .card-container { box-shadow: none !important; } }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .no-print { text-align: center; margin-bottom: 20px; }
        .no-print button { background: #7c3aed; color: white; border: none; padding: 12px 30px; font-size: 16px; cursor: pointer; border-radius: 5px; margin: 0 10px; }
        .no-print button:hover { background: #6d28d9; }
        .card-container { max-width: 700px; margin: 0 auto; background: white; box-shadow: 0 2px 10px rgba(0,0,0,0.1); position: relative; }
        .card { padding: 30px 40px; position: relative; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 200px; height: 200px; opacity: 0.08; z-index: 0; font-size: 150px; text-align: center; line-height: 200px; }
        .content { position: relative; z-index: 1; }
        .header { text-align: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 2px solid #7c3aed; }
        .header h1 { font-size: 28px; font-weight: bold; color: #7c3aed; letter-spacing: 1px; }
        .cert-number { text-align: right; font-size: 11px; color: #666; margin-bottom: 15px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { border: 1px solid #333; padding: 8px 12px; vertical-align: middle; }
        .info-table .label { font-weight: bold; color: #333; font-size: 12px; background: #faf5ff; white-space: nowrap; }
        .info-table .value { font-size: 13px; color: #333; }
        .info-table .label-highlight { font-weight: bold; color: #7c3aed; font-size: 12px; background: #faf5ff; white-space: nowrap; }
        .record-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .record-table th { border: 1px solid #333; padding: 10px 8px; font-size: 11px; font-weight: bold; text-align: center; background: #faf5ff; color: #333; }
        .record-table td { border: 1px solid #333; padding: 10px 8px; font-size: 12px; text-align: center; }
        .record-table .findings-cell { text-align: left; padding: 10px; font-size: 11px; }
        .footer { margin-top: 20px; padding-top: 15px; border-top: 1px solid #ccc; text-align: center; font-size: 10px; color: #666; }
        .program-title { font-size: 11px; font-weight: bold; color: #7c3aed; text-align: center; margin-top: 15px; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ Print / Save as PDF</button>
        <button onclick="window.close()">✕ Close</button>
    </div>
    <div class="card-container">
        <div class="card">
            <div class="watermark">🩺</div>
            <div class="content">
                <div class="header"><h1>Pet Health Checkup Card</h1></div>
                <div class="cert-number">Certificate No: {$certificateNumber}</div>
                <table class="info-table">
                    <tr><td class="label">Pet's Name:</td><td class="value" colspan="3" style="font-weight: bold;">{$petName}</td></tr>
                    <tr><td class="label">Type of Animal:</td><td class="value">{$animalType}</td><td class="label-highlight">Sex:</td><td class="value">{$petGender}</td></tr>
                    <tr><td class="label">Age:</td><td class="value">{$petAge}</td><td class="label-highlight">Breed:</td><td class="value">{$petBreed}</td></tr>
                    <tr><td class="label">Color:</td><td class="value">{$petColor}</td><td class="label-highlight">Date of Birth:</td><td class="value">{$petDob}</td></tr>
                    <tr><td class="label">Owner's Name:</td><td class="value" colspan="3">{$ownerName}</td></tr>
                    <tr><td class="label">Address:</td><td class="value" colspan="3">{$ownerAddress}</td></tr>
                    <tr><td class="label">Cellphone/Telephone Number:</td><td class="value" colspan="3">{$ownerPhone}</td></tr>
                </table>
                <table class="record-table">
                    <thead><tr><th>Date of<br>Checkup</th><th>Findings / Remarks</th><th>Next<br>Visit</th><th>Veterinarian<br>Lic No. PTR</th></tr></thead>
                    <tbody><tr><td>{$serviceDate}</td><td class="findings-cell">{$findings}<br><em style="color: #7c3aed;">{$recommendations}</em></td><td>{$nextServiceDate}</td><td style="font-size: 10px;">{$veterinarianName}<br>Lic: {$licenseNumber}<br>PTR: {$ptrNumber}</td></tr></tbody>
                </table>
                <div class="program-title">PET HEALTH AND WELLNESS PROGRAM</div>
                <div class="footer"><p>Issued on: {$issuedDate} | City Veterinary Office</p></div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate Generic certificate HTML content (fallback)
     */
    public static function generateGenericCertificateHtml($certificate)
    {
        return self::generateVaccinationCertificateHtml($certificate);
    }

    /**
     * Legacy method for backward compatibility
     */
    public static function generateCertificateHtml($certificate)
    {
        return self::generatePdf($certificate);
    }
}