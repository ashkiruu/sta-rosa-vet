<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;

class CertificateService
{
    /**
     * Get certificates storage path
     */
    private static function getCertificatesPath()
    {
        return storage_path('app/certificates.json');
    }

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
     * Load all certificates
     */
    public static function loadCertificates()
    {
        $path = self::getCertificatesPath();
        
        if (!file_exists($path)) {
            $directory = dirname($path);
            if (!is_dir($directory)) {
                mkdir($directory, 0755, true);
            }
            file_put_contents($path, json_encode([], JSON_PRETTY_PRINT));
            return [];
        }
        
        return json_decode(file_get_contents($path), true) ?? [];
    }

    /**
     * Save certificates
     */
    private static function saveCertificates($certificates)
    {
        $path = self::getCertificatesPath();
        file_put_contents($path, json_encode($certificates, JSON_PRETTY_PRINT));
    }

    /**
     * Generate certificate number
     */
    public static function generateCertificateNumber()
    {
        $year = date('Y');
        $certificates = self::loadCertificates();
        $count = count($certificates) + 1;
        return "CVO-{$year}-" . str_pad($count, 5, '0', STR_PAD_LEFT);
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
        $certificates = self::loadCertificates();
        
        $certificateId = uniqid('cert_');
        $certificateNumber = self::generateCertificateNumber();
        
        // Determine service category
        $serviceCategory = self::getServiceCategory($data['service_type'] ?? '');
        
        // Process vaccine data for vaccination service
        $vaccineUsed = null;
        $lotNumber = null;
        $vaccineType = null;
        
        if ($serviceCategory === 'vaccination') {
            $vaccineType = $data['vaccine_type'] ?? null;
            
            if ($vaccineType === 'anti-rabies') {
                // Use the provided vaccine name or default to 'Anti-Rabies Vaccine'
                $vaccineUsed = $data['vaccine_name_rabies'] ?? $data['vaccine_used'] ?? 'Anti-Rabies Vaccine';
                $lotNumber = $data['lot_number'] ?? $data['lot_number_final'] ?? null;
            } elseif ($vaccineType === 'other') {
                $vaccineUsed = $data['vaccine_name_other'] ?? $data['vaccine_used'] ?? null;
                $lotNumber = $data['lot_number_other'] ?? $data['lot_number_final'] ?? null;
            } else {
                // Fallback for existing data
                $vaccineUsed = $data['vaccine_used'] ?? null;
                $lotNumber = $data['lot_number'] ?? null;
            }
        }
        
        $certificate = [
            'id' => $certificateId,
            'certificate_number' => $certificateNumber,
            'appointment_id' => $data['appointment_id'],
            
            // Service Information
            'service_type' => $data['service_type'],
            'service_category' => $serviceCategory,
            
            // Pet Information
            'pet_name' => $data['pet_name'],
            'animal_type' => $data['animal_type'],
            'pet_gender' => $data['pet_gender'],
            'pet_age' => $data['pet_age'],
            'pet_breed' => $data['pet_breed'],
            'pet_color' => $data['pet_color'],
            'pet_dob' => $data['pet_dob'] ?? null,
            
            // Owner Information
            'owner_name' => $data['owner_name'],
            'owner_address' => $data['owner_address'],
            'owner_phone' => $data['owner_phone'],
            'civil_status' => $data['civil_status'] ?? null,
            'years_of_residency' => $data['years_of_residency'] ?? null,
            'owner_birthdate' => $data['owner_birthdate'] ?? null,
            
            // Service Date Fields (common for all)
            'service_date' => $data['service_date'] ?? $data['vaccination_date'] ?? null,
            'next_service_date' => $data['next_service_date'] ?? $data['next_vaccination_date'] ?? null,
            
            // Vaccination-specific fields
            'vaccine_type' => $vaccineType,
            'vaccine_used' => $vaccineUsed,
            'lot_number' => $lotNumber,
            
            // Legacy field mappings for backward compatibility
            'vaccination_date' => $data['service_date'] ?? $data['vaccination_date'] ?? null,
            'next_vaccination_date' => $data['next_service_date'] ?? $data['next_vaccination_date'] ?? null,
            
            // Deworming-specific fields
            'medicine_used' => $data['medicine_used'] ?? null,
            'dosage' => $data['dosage'] ?? null,
            
            // Checkup-specific fields
            'findings' => $data['findings'] ?? null,
            'recommendations' => $data['recommendations'] ?? null,
            
            // Veterinarian Details
            'veterinarian_name' => $data['veterinarian_name'],
            'license_number' => $data['license_number'],
            'ptr_number' => $data['ptr_number'],
            
            // System Fields
            'status' => 'draft',
            'created_at' => now()->format('Y-m-d H:i:s'),
            'created_by' => $data['created_by'] ?? 'Admin',
            'approved_at' => null,
            'approved_by' => null,
            'pdf_path' => null,
        ];
        
        $certificates[$certificateId] = $certificate;
        self::saveCertificates($certificates);
        
        return $certificate;
    }

    /**
     * Update certificate
     */
    public static function updateCertificate($certificateId, $data)
    {
        $certificates = self::loadCertificates();
        
        if (!isset($certificates[$certificateId])) {
            return null;
        }
        
        // Determine service category
        $serviceType = $data['service_type'] ?? $certificates[$certificateId]['service_type'] ?? '';
        $serviceCategory = self::getServiceCategory($serviceType);
        
        // Process vaccine data for vaccination service
        if ($serviceCategory === 'vaccination') {
            $vaccineType = $data['vaccine_type'] ?? $certificates[$certificateId]['vaccine_type'] ?? null;
            
            if ($vaccineType === 'anti-rabies') {
                // Use the provided vaccine name or keep existing
                $data['vaccine_used'] = $data['vaccine_name_rabies'] ?? $data['vaccine_used'] ?? $certificates[$certificateId]['vaccine_used'] ?? 'Anti-Rabies Vaccine';
                $data['lot_number'] = $data['lot_number'] ?? $data['lot_number_final'] ?? $certificates[$certificateId]['lot_number'] ?? null;
            } elseif ($vaccineType === 'other') {
                $data['vaccine_used'] = $data['vaccine_name_other'] ?? $data['vaccine_used'] ?? $certificates[$certificateId]['vaccine_used'] ?? null;
                $data['lot_number'] = $data['lot_number_other'] ?? $data['lot_number_final'] ?? $certificates[$certificateId]['lot_number'] ?? null;
            }
            
            $data['vaccine_type'] = $vaccineType;
        }
        
        // Map service_date to vaccination_date for backward compatibility
        if (isset($data['service_date'])) {
            $data['vaccination_date'] = $data['service_date'];
        }
        if (isset($data['next_service_date'])) {
            $data['next_vaccination_date'] = $data['next_service_date'];
        }
        
        $allowedFields = [
            'pet_name', 'animal_type', 'pet_gender', 'pet_age', 'pet_breed', 
            'pet_color', 'pet_dob', 'owner_name', 'owner_address', 'owner_phone',
            'civil_status', 'years_of_residency', 'owner_birthdate',
            'service_type', 'service_category', 'service_date', 'next_service_date',
            'vaccination_date', 'lot_number', 'next_vaccination_date',
            'vaccine_type', 'vaccine_used',
            'medicine_used', 'dosage',
            'findings', 'recommendations',
            'veterinarian_name', 'license_number', 'ptr_number'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $certificates[$certificateId][$field] = $data[$field];
            }
        }
        
        // Update service category
        $certificates[$certificateId]['service_category'] = $serviceCategory;
        $certificates[$certificateId]['updated_at'] = now()->format('Y-m-d H:i:s');
        
        self::saveCertificates($certificates);
        
        return $certificates[$certificateId];
    }

    /**
     * Approve certificate and generate PDF
     */
    public static function approveCertificate($certificateId, $approvedBy = 'Admin')
    {
        $certificates = self::loadCertificates();
        
        if (!isset($certificates[$certificateId])) {
            return null;
        }
        
        $certificate = $certificates[$certificateId];
        
        // Generate PDF
        $pdfPath = self::generatePdf($certificate);
        
        // Update certificate status
        $certificates[$certificateId]['status'] = 'approved';
        $certificates[$certificateId]['approved_at'] = now()->format('Y-m-d H:i:s');
        $certificates[$certificateId]['approved_by'] = $approvedBy;
        $certificates[$certificateId]['pdf_path'] = $pdfPath;
        
        self::saveCertificates($certificates);
        
        // Sync appointment status to 'Completed'
        self::syncAppointmentStatus($certificate['appointment_id']);
        
        return $certificates[$certificateId];
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
            
            \Log::info("Certificate approval synced appointment {$appointmentId} status to Completed");
            return true;
        }
        
        return false;
    }

    /**
     * Get certificate by ID
     */
    public static function getCertificate($certificateId)
    {
        $certificates = self::loadCertificates();
        return $certificates[$certificateId] ?? null;
    }

    /**
     * Get certificate by appointment ID
     */
    public static function getCertificateByAppointment($appointmentId)
    {
        $certificates = self::loadCertificates();
        
        foreach ($certificates as $cert) {
            if ($cert['appointment_id'] == $appointmentId) {
                return $cert;
            }
        }
        
        return null;
    }

    /**
     * Get all certificates for a pet owner (by user_id)
     */
    public static function getCertificatesByOwner($userId)
    {
        $certificates = self::loadCertificates();
        $appointments = Appointment::where('User_ID', $userId)->pluck('Appointment_ID')->toArray();
        
        return array_filter($certificates, function($cert) use ($appointments) {
            return in_array($cert['appointment_id'], $appointments) && $cert['status'] === 'approved';
        });
    }

    /**
     * Delete certificate
     */
    public static function deleteCertificate($certificateId)
    {
        $certificates = self::loadCertificates();
        
        if (!isset($certificates[$certificateId])) {
            return false;
        }
        
        if (!empty($certificates[$certificateId]['pdf_path'])) {
            $pdfFullPath = storage_path('app/public/' . $certificates[$certificateId]['pdf_path']);
            if (file_exists($pdfFullPath)) {
                unlink($pdfFullPath);
            }
        }
        
        unset($certificates[$certificateId]);
        self::saveCertificates($certificates);
        
        return true;
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
     * Generate Vaccination certificate HTML content (Pet Vaccination Card style)
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
        body { 
            font-family: Arial, sans-serif; 
            background: #f5f5f5; 
            padding: 20px; 
        }
        .no-print { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .no-print button { 
            background: #333; 
            color: white; 
            border: none; 
            padding: 12px 30px; 
            font-size: 16px; 
            cursor: pointer; 
            border-radius: 5px; 
            margin: 0 10px; 
        }
        .no-print button:hover { 
            background: #555; 
        }
        .card-container { 
            max-width: 700px; 
            margin: 0 auto; 
            background: white; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            position: relative;
        }
        .card {
            padding: 30px 40px;
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            opacity: 0.08;
            z-index: 0;
            font-size: 150px;
            text-align: center;
            line-height: 200px;
        }
        .content {
            position: relative;
            z-index: 1;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }
        .header h1 {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            letter-spacing: 1px;
        }
        .cert-number {
            text-align: right;
            font-size: 11px;
            color: #666;
            margin-bottom: 15px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            border: 1px solid #333;
            padding: 8px 12px;
            vertical-align: middle;
        }
        .info-table .label {
            font-weight: bold;
            color: #333;
            font-size: 12px;
            background: #fafafa;
            white-space: nowrap;
        }
        .info-table .value {
            font-size: 13px;
            color: #333;
        }
        .info-table .label-highlight {
            font-weight: bold;
            color: #b8860b;
            font-size: 12px;
            background: #fafafa;
            white-space: nowrap;
        }
        .vacc-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .vacc-table th {
            border: 1px solid #333;
            padding: 10px 8px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            background: #fafafa;
            color: #333;
        }
        .vacc-table td {
            border: 1px solid #333;
            padding: 10px 8px;
            font-size: 12px;
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .program-title {
            font-size: 11px;
            font-weight: bold;
            color: #333;
            text-align: center;
            margin-top: 15px;
            letter-spacing: 1px;
        }
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
                <div class="header">
                    <h1>Pet Vaccination Card</h1>
                </div>
                
                <div class="cert-number">Certificate No: {$certificateNumber}</div>
                
                <table class="info-table">
                    <tr>
                        <td class="label">Pet's Name:</td>
                        <td class="value" colspan="3" style="font-weight: bold;">{$petName}</td>
                    </tr>
                    <tr>
                        <td class="label">Type of Animal:</td>
                        <td class="value">{$animalType}</td>
                        <td class="label-highlight">Sex:</td>
                        <td class="value">{$petGender}</td>
                    </tr>
                    <tr>
                        <td class="label">Age:</td>
                        <td class="value">{$petAge}</td>
                        <td class="label-highlight">Breed:</td>
                        <td class="value">{$petBreed}</td>
                    </tr>
                    <tr>
                        <td class="label">Color:</td>
                        <td class="value">{$petColor}</td>
                        <td class="label-highlight">Date of Birth:</td>
                        <td class="value">{$petDob}</td>
                    </tr>
                    <tr>
                        <td class="label">Owner's Name:</td>
                        <td class="value" colspan="3">{$ownerName}</td>
                    </tr>
                    <tr>
                        <td class="label">Address:</td>
                        <td class="value" colspan="3">{$ownerAddress}</td>
                    </tr>
                    <tr>
                        <td class="label">Cellphone/Telephone Number:</td>
                        <td class="value" colspan="3">{$ownerPhone}</td>
                    </tr>
                </table>
                
                <table class="vacc-table">
                    <thead>
                        <tr>
                            <th>Date of<br>Vaccination</th>
                            <th>Vaccine<br>Used</th>
                            <th>Lot No./<br>Batch No.</th>
                            <th>Date of Next<br>Vaccination</th>
                            <th>Veterinarian<br>Lic No. PTR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{$vaccinationDate}</td>
                            <td>{$vaccineUsed}</td>
                            <td>{$lotNumber}</td>
                            <td>{$nextVaccinationDate}</td>
                            <td style="font-size: 10px;">{$veterinarianName}<br>Lic: {$licenseNumber}<br>PTR: {$ptrNumber}</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="program-title">RABIES PREVENTION AND CONTROL PROGRAM</div>
                
                <div class="footer">
                    <p>Issued on: {$issuedDate} | City Veterinary Office</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate Deworming certificate HTML content (Card style)
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
        @media print {
            body { margin: 0; padding: 10px; }
            .no-print { display: none !important; }
            .card-container { box-shadow: none !important; }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            background: #f5f5f5; 
            padding: 20px; 
        }
        .no-print { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .no-print button { 
            background: #059669; 
            color: white; 
            border: none; 
            padding: 12px 30px; 
            font-size: 16px; 
            cursor: pointer; 
            border-radius: 5px; 
            margin: 0 10px; 
        }
        .no-print button:hover { 
            background: #047857; 
        }
        .card-container { 
            max-width: 700px; 
            margin: 0 auto; 
            background: white; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            position: relative;
        }
        .card {
            padding: 30px 40px;
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            opacity: 0.08;
            z-index: 0;
            font-size: 150px;
            text-align: center;
            line-height: 200px;
        }
        .content {
            position: relative;
            z-index: 1;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #059669;
        }
        .header h1 {
            font-size: 28px;
            font-weight: bold;
            color: #059669;
            letter-spacing: 1px;
        }
        .cert-number {
            text-align: right;
            font-size: 11px;
            color: #666;
            margin-bottom: 15px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            border: 1px solid #333;
            padding: 8px 12px;
            vertical-align: middle;
        }
        .info-table .label {
            font-weight: bold;
            color: #333;
            font-size: 12px;
            background: #f0fdf4;
            white-space: nowrap;
        }
        .info-table .value {
            font-size: 13px;
            color: #333;
        }
        .info-table .label-highlight {
            font-weight: bold;
            color: #059669;
            font-size: 12px;
            background: #f0fdf4;
            white-space: nowrap;
        }
        .record-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .record-table th {
            border: 1px solid #333;
            padding: 10px 8px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            background: #f0fdf4;
            color: #333;
        }
        .record-table td {
            border: 1px solid #333;
            padding: 10px 8px;
            font-size: 12px;
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .program-title {
            font-size: 11px;
            font-weight: bold;
            color: #059669;
            text-align: center;
            margin-top: 15px;
            letter-spacing: 1px;
        }
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
                <div class="header">
                    <h1>Pet Deworming Card</h1>
                </div>
                
                <div class="cert-number">Certificate No: {$certificateNumber}</div>
                
                <table class="info-table">
                    <tr>
                        <td class="label">Pet's Name:</td>
                        <td class="value" colspan="3" style="font-weight: bold;">{$petName}</td>
                    </tr>
                    <tr>
                        <td class="label">Type of Animal:</td>
                        <td class="value">{$animalType}</td>
                        <td class="label-highlight">Sex:</td>
                        <td class="value">{$petGender}</td>
                    </tr>
                    <tr>
                        <td class="label">Age:</td>
                        <td class="value">{$petAge}</td>
                        <td class="label-highlight">Breed:</td>
                        <td class="value">{$petBreed}</td>
                    </tr>
                    <tr>
                        <td class="label">Color:</td>
                        <td class="value">{$petColor}</td>
                        <td class="label-highlight">Date of Birth:</td>
                        <td class="value">{$petDob}</td>
                    </tr>
                    <tr>
                        <td class="label">Owner's Name:</td>
                        <td class="value" colspan="3">{$ownerName}</td>
                    </tr>
                    <tr>
                        <td class="label">Address:</td>
                        <td class="value" colspan="3">{$ownerAddress}</td>
                    </tr>
                    <tr>
                        <td class="label">Cellphone/Telephone Number:</td>
                        <td class="value" colspan="3">{$ownerPhone}</td>
                    </tr>
                </table>
                
                <table class="record-table">
                    <thead>
                        <tr>
                            <th>Date of<br>Deworming</th>
                            <th>Medicine<br>Used</th>
                            <th>Dosage</th>
                            <th>Date of Next<br>Deworming</th>
                            <th>Veterinarian<br>Lic No. PTR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{$serviceDate}</td>
                            <td>{$medicineUsed}</td>
                            <td>{$dosage}</td>
                            <td>{$nextServiceDate}</td>
                            <td style="font-size: 10px;">{$veterinarianName}<br>Lic: {$licenseNumber}<br>PTR: {$ptrNumber}</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="program-title">PET HEALTH AND WELLNESS PROGRAM</div>
                
                <div class="footer">
                    <p>Issued on: {$issuedDate} | City Veterinary Office</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate Checkup certificate HTML content (Card style)
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
        @media print {
            body { margin: 0; padding: 10px; }
            .no-print { display: none !important; }
            .card-container { box-shadow: none !important; }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            background: #f5f5f5; 
            padding: 20px; 
        }
        .no-print { 
            text-align: center; 
            margin-bottom: 20px; 
        }
        .no-print button { 
            background: #7c3aed; 
            color: white; 
            border: none; 
            padding: 12px 30px; 
            font-size: 16px; 
            cursor: pointer; 
            border-radius: 5px; 
            margin: 0 10px; 
        }
        .no-print button:hover { 
            background: #6d28d9; 
        }
        .card-container { 
            max-width: 700px; 
            margin: 0 auto; 
            background: white; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
            position: relative;
        }
        .card {
            padding: 30px 40px;
            position: relative;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            opacity: 0.08;
            z-index: 0;
            font-size: 150px;
            text-align: center;
            line-height: 200px;
        }
        .content {
            position: relative;
            z-index: 1;
        }
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #7c3aed;
        }
        .header h1 {
            font-size: 28px;
            font-weight: bold;
            color: #7c3aed;
            letter-spacing: 1px;
        }
        .cert-number {
            text-align: right;
            font-size: 11px;
            color: #666;
            margin-bottom: 15px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td {
            border: 1px solid #333;
            padding: 8px 12px;
            vertical-align: middle;
        }
        .info-table .label {
            font-weight: bold;
            color: #333;
            font-size: 12px;
            background: #faf5ff;
            white-space: nowrap;
        }
        .info-table .value {
            font-size: 13px;
            color: #333;
        }
        .info-table .label-highlight {
            font-weight: bold;
            color: #7c3aed;
            font-size: 12px;
            background: #faf5ff;
            white-space: nowrap;
        }
        .record-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .record-table th {
            border: 1px solid #333;
            padding: 10px 8px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            background: #faf5ff;
            color: #333;
        }
        .record-table td {
            border: 1px solid #333;
            padding: 10px 8px;
            font-size: 12px;
            text-align: center;
        }
        .record-table .findings-cell {
            text-align: left;
            padding: 10px;
            font-size: 11px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .program-title {
            font-size: 11px;
            font-weight: bold;
            color: #7c3aed;
            text-align: center;
            margin-top: 15px;
            letter-spacing: 1px;
        }
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
                <div class="header">
                    <h1>Pet Health Checkup Card</h1>
                </div>
                
                <div class="cert-number">Certificate No: {$certificateNumber}</div>
                
                <table class="info-table">
                    <tr>
                        <td class="label">Pet's Name:</td>
                        <td class="value" colspan="3" style="font-weight: bold;">{$petName}</td>
                    </tr>
                    <tr>
                        <td class="label">Type of Animal:</td>
                        <td class="value">{$animalType}</td>
                        <td class="label-highlight">Sex:</td>
                        <td class="value">{$petGender}</td>
                    </tr>
                    <tr>
                        <td class="label">Age:</td>
                        <td class="value">{$petAge}</td>
                        <td class="label-highlight">Breed:</td>
                        <td class="value">{$petBreed}</td>
                    </tr>
                    <tr>
                        <td class="label">Color:</td>
                        <td class="value">{$petColor}</td>
                        <td class="label-highlight">Date of Birth:</td>
                        <td class="value">{$petDob}</td>
                    </tr>
                    <tr>
                        <td class="label">Owner's Name:</td>
                        <td class="value" colspan="3">{$ownerName}</td>
                    </tr>
                    <tr>
                        <td class="label">Address:</td>
                        <td class="value" colspan="3">{$ownerAddress}</td>
                    </tr>
                    <tr>
                        <td class="label">Cellphone/Telephone Number:</td>
                        <td class="value" colspan="3">{$ownerPhone}</td>
                    </tr>
                </table>
                
                <table class="record-table">
                    <thead>
                        <tr>
                            <th>Date of<br>Checkup</th>
                            <th>Findings / Remarks</th>
                            <th>Next<br>Visit</th>
                            <th>Veterinarian<br>Lic No. PTR</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>{$serviceDate}</td>
                            <td class="findings-cell">{$findings}<br><em style="color: #7c3aed;">{$recommendations}</em></td>
                            <td>{$nextServiceDate}</td>
                            <td style="font-size: 10px;">{$veterinarianName}<br>Lic: {$licenseNumber}<br>PTR: {$ptrNumber}</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="program-title">PET HEALTH AND WELLNESS PROGRAM</div>
                
                <div class="footer">
                    <p>Issued on: {$issuedDate} | City Veterinary Office</p>
                </div>
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
        // Use vaccination template as fallback
        return self::generateVaccinationCertificateHtml($certificate);
    }

    /**
     * Legacy method for backward compatibility
     */
    public static function generateCertificateHtml($certificate)
    {
        return self::generatePdf($certificate);
    }

    /**
     * Get all certificates with pagination-like array
     */
    public static function getAllCertificates($status = null)
    {
        $certificates = self::loadCertificates();
        
        if ($status) {
            $certificates = array_filter($certificates, function($cert) use ($status) {
                return $cert['status'] === $status;
            });
        }
        
        usort($certificates, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $certificates;
    }
}