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
     * Create a new certificate
     */
    public static function createCertificate($data)
    {
        $certificates = self::loadCertificates();
        
        $certificateId = uniqid('cert_');
        $certificateNumber = self::generateCertificateNumber();
        
        $certificate = [
            'id' => $certificateId,
            'certificate_number' => $certificateNumber,
            'appointment_id' => $data['appointment_id'],
            
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
            
            // Vaccination/Service Details
            'service_type' => $data['service_type'],
            'vaccination_date' => $data['vaccination_date'],
            'lot_number' => $data['lot_number'],
            'next_vaccination_date' => $data['next_vaccination_date'] ?? null,
            'vaccine_used' => $data['vaccine_used'],
            
            // Veterinarian Details
            'veterinarian_name' => $data['veterinarian_name'],
            'license_number' => $data['license_number'],
            'ptr_number' => $data['ptr_number'],
            
            // System Fields
            'status' => 'draft', // draft, approved, issued
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
        
        // Update allowed fields
        $allowedFields = [
            'pet_name', 'animal_type', 'pet_gender', 'pet_age', 'pet_breed', 
            'pet_color', 'pet_dob', 'owner_name', 'owner_address', 'owner_phone',
            'civil_status', 'years_of_residency', 'owner_birthdate',
            'service_type', 'vaccination_date', 'lot_number', 'next_vaccination_date',
            'vaccine_used', 'veterinarian_name', 'license_number', 'ptr_number'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $certificates[$certificateId][$field] = $data[$field];
            }
        }
        
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
        
        return $certificates[$certificateId];
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
        
        // Delete PDF if exists
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
     * Generate PDF certificate using HTML to PDF conversion
     * No external libraries - uses browser print / HTML download
     */
    public static function generatePdf($certificate)
    {
        $html = self::generateCertificateHtml($certificate);
        
        // Save as HTML file (can be printed to PDF by browser)
        $filename = 'certificates/certificate_' . $certificate['id'] . '.html';
        $fullPath = storage_path('app/public/' . $filename);
        
        // Ensure directory exists
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($fullPath, $html);
        
        return $filename;
    }

    /**
     * Generate certificate HTML content
     */
    public static function generateCertificateHtml($certificate)
    {
        $certificateNumber = $certificate['certificate_number'];
        $petName = htmlspecialchars($certificate['pet_name']);
        $animalType = htmlspecialchars($certificate['animal_type']);
        $petGender = htmlspecialchars($certificate['pet_gender']);
        $petAge = htmlspecialchars($certificate['pet_age']);
        $petBreed = htmlspecialchars($certificate['pet_breed']);
        $petColor = htmlspecialchars($certificate['pet_color']);
        $petDob = $certificate['pet_dob'] ? date('F d, Y', strtotime($certificate['pet_dob'])) : 'N/A';
        
        $ownerName = htmlspecialchars($certificate['owner_name']);
        $ownerAddress = htmlspecialchars($certificate['owner_address']);
        $ownerPhone = htmlspecialchars($certificate['owner_phone']);
        $civilStatus = htmlspecialchars($certificate['civil_status'] ?? 'N/A');
        $yearsOfResidency = htmlspecialchars($certificate['years_of_residency'] ?? 'N/A');
        $ownerBirthdate = isset($certificate['owner_birthdate']) && $certificate['owner_birthdate'] 
            ? date('F d, Y', strtotime($certificate['owner_birthdate'])) 
            : 'N/A';
        
        $serviceType = htmlspecialchars($certificate['service_type']);
        $vaccinationDate = $certificate['vaccination_date'] ? date('F d, Y', strtotime($certificate['vaccination_date'])) : 'N/A';
        $lotNumber = htmlspecialchars($certificate['lot_number']);
        $nextVaccinationDate = $certificate['next_vaccination_date'] ? date('F d, Y', strtotime($certificate['next_vaccination_date'])) : 'N/A';
        $vaccineUsed = htmlspecialchars($certificate['vaccine_used']);
        
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
    <title>Veterinary Certificate - {$certificateNumber}</title>
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none !important; }
            .certificate-container { box-shadow: none !important; }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            background: #f0f0f0;
            padding: 20px;
        }
        
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .no-print button {
            background: #dc2626;
            color: white;
            border: none;
            padding: 12px 30px;
            font-size: 16px;
            cursor: pointer;
            border-radius: 5px;
            margin: 0 10px;
        }
        
        .no-print button:hover {
            background: #b91c1c;
        }
        
        .certificate-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .certificate {
            padding: 40px;
            border: 3px solid #1e40af;
            margin: 10px;
            position: relative;
        }
        
        .certificate::before {
            content: '';
            position: absolute;
            top: 5px;
            left: 5px;
            right: 5px;
            bottom: 5px;
            border: 1px solid #1e40af;
            pointer-events: none;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 20px;
        }
        
        .header-top {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 10px;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            background: #1e40af;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
        }
        
        .header h1 {
            color: #1e40af;
            font-size: 28px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        
        .header h2 {
            color: #dc2626;
            font-size: 22px;
            margin-top: 10px;
            text-transform: uppercase;
            letter-spacing: 3px;
        }
        
        .header p {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }
        
        .certificate-number {
            text-align: right;
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .certificate-number strong {
            color: #1e40af;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            background: #1e40af;
            color: white;
            padding: 8px 15px;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 30px;
        }
        
        .info-row {
            display: flex;
            border-bottom: 1px dotted #ccc;
            padding: 5px 0;
        }
        
        .info-label {
            font-weight: bold;
            color: #333;
            min-width: 150px;
            font-size: 13px;
        }
        
        .info-value {
            color: #1e40af;
            font-size: 13px;
        }
        
        .info-row.full-width {
            grid-column: span 2;
        }
        
        .certification-text {
            text-align: center;
            padding: 20px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            margin: 20px 0;
            font-style: italic;
            line-height: 1.8;
        }
        
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
        }
        
        .signature-box {
            text-align: center;
            width: 45%;
        }
        
        .signature-line {
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 50px;
        }
        
        .signature-name {
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
        }
        
        .signature-title {
            font-size: 12px;
            color: #666;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
            font-size: 11px;
            color: #666;
        }
        
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 100px;
            color: rgba(30, 64, 175, 0.05);
            font-weight: bold;
            pointer-events: none;
            white-space: nowrap;
        }
        
        .stamp {
            position: absolute;
            bottom: 100px;
            right: 60px;
            width: 100px;
            height: 100px;
            border: 3px solid #dc2626;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transform: rotate(-15deg);
            opacity: 0.8;
        }
        
        .stamp-inner {
            text-align: center;
            color: #dc2626;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()">🖨️ Print / Save as PDF</button>
        <button onclick="window.close()">✕ Close</button>
    </div>
    
    <div class="certificate-container">
        <div class="certificate">
            <div class="watermark">OFFICIAL</div>
            
            <div class="header">
                <div class="header-top">
                    <div class="logo">🐾</div>
                    <div>
                        <h1>City Veterinary Office</h1>
                        <h2>Vaccination Certificate</h2>
                        <p>Official Document • Republic of the Philippines</p>
                    </div>
                </div>
            </div>
            
            <div class="certificate-number">
                Certificate No: <strong>{$certificateNumber}</strong>
            </div>
            
            <div class="section">
                <div class="section-title">Pet Information</div>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Pet's Name:</span>
                        <span class="info-value">{$petName}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Type of Animal:</span>
                        <span class="info-value">{$animalType}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Gender/Sex:</span>
                        <span class="info-value">{$petGender}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Age:</span>
                        <span class="info-value">{$petAge}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Breed:</span>
                        <span class="info-value">{$petBreed}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Color:</span>
                        <span class="info-value">{$petColor}</span>
                    </div>
                    <div class="info-row full-width">
                        <span class="info-label">Date of Birth:</span>
                        <span class="info-value">{$petDob}</span>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Owner Information</div>
                <div class="info-grid">
                    <div class="info-row full-width">
                        <span class="info-label">Owner's Name:</span>
                        <span class="info-value">{$ownerName}</span>
                    </div>
                    <div class="info-row full-width">
                        <span class="info-label">Complete Address:</span>
                        <span class="info-value">{$ownerAddress}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Civil Status:</span>
                        <span class="info-value">{$civilStatus}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Years of Residency:</span>
                        <span class="info-value">{$yearsOfResidency}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Contact Number:</span>
                        <span class="info-value">{$ownerPhone}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Birthdate:</span>
                        <span class="info-value">{$ownerBirthdate}</span>
                    </div>
                </div>
            </div>
            
            <div class="section">
                <div class="section-title">Vaccination Details</div>
                <div class="info-grid">
                    <div class="info-row">
                        <span class="info-label">Service Type:</span>
                        <span class="info-value">{$serviceType}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Vaccine Used:</span>
                        <span class="info-value">{$vaccineUsed}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date of Vaccination:</span>
                        <span class="info-value">{$vaccinationDate}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Lot/Batch Number:</span>
                        <span class="info-value">{$lotNumber}</span>
                    </div>
                    <div class="info-row full-width">
                        <span class="info-label">Next Vaccination:</span>
                        <span class="info-value">{$nextVaccinationDate}</span>
                    </div>
                </div>
            </div>
            
            <div class="certification-text">
                This is to certify that the above-mentioned animal has been vaccinated/treated
                at the City Veterinary Office and is found to be in good health condition
                at the time of examination.
            </div>
            
            <div class="section">
                <div class="section-title">Attending Veterinarian</div>
                <div class="info-grid">
                    <div class="info-row full-width">
                        <span class="info-label">Veterinarian:</span>
                        <span class="info-value">{$veterinarianName}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">License No.:</span>
                        <span class="info-value">{$licenseNumber}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">PTR No.:</span>
                        <span class="info-value">{$ptrNumber}</span>
                    </div>
                </div>
            </div>
            
            <div class="signature-section">
                <div class="signature-box">
                    <div class="signature-line">
                        <div class="signature-name">{$veterinarianName}</div>
                        <div class="signature-title">Licensed Veterinarian</div>
                    </div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">
                        <div class="signature-name">City Veterinarian</div>
                        <div class="signature-title">Head of Office</div>
                    </div>
                </div>
            </div>
            
            <div class="stamp">
                <div class="stamp-inner">
                    VERIFIED<br>
                    ✓<br>
                    CVO
                </div>
            </div>
            
            <div class="footer">
                <p>Issued on: {$issuedDate}</p>
                <p>This certificate is valid for one (1) year from the date of vaccination unless otherwise specified.</p>
                <p>City Veterinary Office • Contact: (000) 000-0000 • Email: cvo@city.gov.ph</p>
            </div>
        </div>
    </div>
    
    <script>
        // Auto-prompt print dialog when opened
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
HTML;
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
        
        // Sort by created_at descending
        usort($certificates, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return $certificates;
    }
}