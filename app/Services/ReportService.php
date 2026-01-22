<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use App\Models\Barangay;
use App\Models\Species;
use Carbon\Carbon;

class ReportService
{
    /**
     * Get reports storage path
     */
    private static function getReportsPath()
    {
        return storage_path('app/reports.json');
    }

    /**
     * Get PDF storage directory
     */
    private static function getPdfDirectory()
    {
        $dir = storage_path('app/public/reports');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    /**
     * Load all reports
     */
    public static function loadReports()
    {
        $path = self::getReportsPath();
        
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
     * Save reports
     */
    private static function saveReports($reports)
    {
        $path = self::getReportsPath();
        file_put_contents($path, json_encode($reports, JSON_PRETTY_PRINT));
    }

    /**
     * Generate report number
     */
    public static function generateReportNumber($type = 'WEEKLY')
    {
        $year = date('Y');
        $week = date('W');
        $reports = self::loadReports();
        $count = count($reports) + 1;
        return "RPT-{$type}-{$year}-W{$week}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get weekly date range
     */
    /*public static function getWeeklyDateRange($weekOffset = 0)
    {
        $now = Carbon::now()->subWeeks($weekOffset);
        $startOfWeek = $now->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $now->copy()->endOfWeek(Carbon::SUNDAY);
        
        return [
            'start' => $startOfWeek,
            'end' => $endOfWeek,
            'week_number' => $startOfWeek->weekOfYear,
            'year' => $startOfWeek->year,
        ];
    }
/*
    /**
     * Get species name by ID
     */
    private static function getSpeciesName($speciesId)
    {
        if (!$speciesId) {
            return 'N/A';
        }
        
        $species = Species::find($speciesId);
        return $species ? $species->Species_Name : 'N/A';
    }

    /**
     * Get barangay name by ID
     */
    private static function getBarangayName($barangayId)
    {
        if (!$barangayId) {
            return 'N/A';
        }
        
        $barangay = Barangay::find($barangayId);
        return $barangay ? $barangay->Barangay_Name : 'N/A';
    }

    /**
     * Get Anti-Rabies Vaccination Data (Walk-in)
     */
    public static function getAntiRabiesData($startDate, $endDate)
    {
        $appointments = Appointment::with(['user', 'user.barangay', 'pet', 'pet.species', 'service'])
            ->whereBetween('Date', [$startDate, $endDate])
            ->where('Status', 'Completed')
            ->whereHas('service', function($query) {
                $query->where('Service_Name', 'LIKE', '%rabies%')
                      ->orWhere('Service_Name', 'LIKE', '%anti-rabies%')
                      ->orWhere('Service_Name', 'LIKE', '%vaccination%');
            })
            ->orderBy('Date', 'asc')
            ->get();

        $data = [];
        foreach ($appointments as $appointment) {
            $user = $appointment->user;
            $pet = $appointment->pet;
            
            $certificate = CertificateService::getCertificateByAppointment($appointment->Appointment_ID);
            
            $barangayName = $user->barangay ? $user->barangay->Barangay_Name : self::getBarangayName($user->Barangay_ID ?? null);
            $speciesName = $pet->species ? $pet->species->Species_Name : self::getSpeciesName($pet->Species_ID ?? null);
            
            if ($certificate && !empty($certificate['owner_address'])) {
                $completeAddress = $certificate['owner_address'];
            } else {
                $completeAddress = $user->Address ?? '';
                if ($barangayName && $barangayName !== 'N/A') {
                    $completeAddress = trim($completeAddress . ', Brgy. ' . $barangayName);
                }
            }
            
            $civilStatus = $certificate['civil_status'] ?? 'N/A';
            $yearsOfResidency = $certificate['years_of_residency'] ?? 'N/A';
            
            $data[] = [
                'client_name' => $certificate['owner_name'] ?? trim(($user->First_Name ?? '') . ' ' . ($user->Middle_Name ?? '') . ' ' . ($user->Last_Name ?? '')),
                'complete_address' => $completeAddress ?: 'N/A',
                'civil_status' => $civilStatus,
                'years_of_residency' => $yearsOfResidency,
                'pet_name' => $certificate['pet_name'] ?? $pet->Pet_Name ?? 'N/A',
                'species' => $certificate['animal_type'] ?? $speciesName,
                'age' => $certificate['pet_age'] ?? $pet->Age ?? self::calculatePetAge($pet->Date_of_Birth ?? null),
                'color' => $certificate['pet_color'] ?? $pet->Color ?? 'N/A',
                'gender' => $certificate['pet_gender'] ?? $pet->Sex ?? 'N/A',
                'date' => Carbon::parse($appointment->Date)->format('M d, Y'),
            ];
        }

        return $data;
    }

    /**
     * Get Routine Services Report Data
     */
    public static function getRoutineServicesData($startDate, $endDate)
    {
        $appointments = Appointment::with(['user', 'user.barangay', 'pet', 'pet.species', 'service'])
            ->whereBetween('Date', [$startDate, $endDate])
            ->where('Status', 'Completed')
            ->orderBy('Date', 'asc')
            ->get();

        $data = [];
        foreach ($appointments as $appointment) {
            $user = $appointment->user;
            $pet = $appointment->pet;
            
            $certificate = CertificateService::getCertificateByAppointment($appointment->Appointment_ID);
            
            $barangayName = $user->barangay ? $user->barangay->Barangay_Name : self::getBarangayName($user->Barangay_ID ?? null);
            $speciesName = $pet->species ? $pet->species->Species_Name : self::getSpeciesName($pet->Species_ID ?? null);
            
            $birthdate = 'N/A';
            if ($certificate && !empty($certificate['owner_birthdate'])) {
                $birthdate = Carbon::parse($certificate['owner_birthdate'])->format('M d, Y');
            }
            
            $data[] = [
                'client_name' => $certificate['owner_name'] ?? trim(($user->First_Name ?? '') . ' ' . ($user->Last_Name ?? '')),
                'barangay' => $barangayName,
                'birthdate' => $birthdate,
                'contact_number' => $certificate['owner_phone'] ?? $user->Contact_Number ?? 'N/A',
                'service_rendered' => $certificate['service_type'] ?? $appointment->service->Service_Name ?? 'N/A',
                'pet_name' => $certificate['pet_name'] ?? $pet->Pet_Name ?? 'N/A',
                'species' => $certificate['animal_type'] ?? $speciesName,
                'gender' => $certificate['pet_gender'] ?? $pet->Sex ?? 'N/A',
                'date' => Carbon::parse($appointment->Date)->format('M d, Y'),
            ];
        }

        return $data;
    }

    /**
     * Calculate pet age from date of birth
     */
    private static function calculatePetAge($dateOfBirth)
    {
        if (!$dateOfBirth) {
            return 'N/A';
        }

        try {
            $dob = Carbon::parse($dateOfBirth);
            $now = Carbon::now();
            $years = $dob->diffInYears($now);
            $months = $dob->copy()->addYears($years)->diffInMonths($now);

            if ($years > 0 && $months > 0) {
                return "{$years} yr(s), {$months} mo(s)";
            } elseif ($years > 0) {
                return "{$years} year(s)";
            } else {
                return "{$months} month(s)";
            }
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    /**
     * Get weekly statistics summary
     * FIXED: Now also counts certificates from the JSON file to ensure sync
     */
    public static function getWeeklySummary($startDate, $endDate)
    {
        // Count from appointments table
        $totalAppointments = Appointment::whereBetween('Date', [$startDate, $endDate])->count();
        $completedAppointments = Appointment::whereBetween('Date', [$startDate, $endDate])
            ->where('Status', 'Completed')->count();
        $pendingAppointments = Appointment::whereBetween('Date', [$startDate, $endDate])
            ->where('Status', 'Pending')->count();
        $approvedAppointments = Appointment::whereBetween('Date', [$startDate, $endDate])
            ->where('Status', 'Approved')->count();

        // BUGFIX: Also count certificates issued in this period (from JSON)
        // This handles cases where certificates were created but appointment status wasn't updated
        $certificatesIssued = self::countCertificatesInPeriod($startDate, $endDate);
        
        // Use the higher of the two counts to ensure we don't undercount
        // (in case appointment status and certificate creation are out of sync)
        $completedCount = max($completedAppointments, $certificatesIssued);

        return [
            'total_appointments' => $totalAppointments,
            'completed' => $completedCount,
            'pending' => $pendingAppointments,
            'approved' => $approvedAppointments,
            'certificates_issued' => $certificatesIssued,
        ];
    }

    /**
     * Count certificates issued in a date range
     * This ensures we capture certificates even if appointment status wasn't updated
     */
    private static function countCertificatesInPeriod($startDate, $endDate)
    {
        $certificates = CertificateService::getAllCertificates('approved');
        $count = 0;
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        foreach ($certificates as $cert) {
            // Check by vaccination_date (the service date)
            if (!empty($cert['vaccination_date'])) {
                $certDate = Carbon::parse($cert['vaccination_date']);
                if ($certDate->between($start, $end)) {
                    $count++;
                    continue;
                }
            }
            
            // Also check by approved_at date as fallback
            if (!empty($cert['approved_at'])) {
                $approvedDate = Carbon::parse($cert['approved_at']);
                if ($approvedDate->between($start, $end)) {
                    $count++;
                }
            }
        }
        
        return $count;
    }

    /**
     * Sync appointment status with certificate
     * Call this when a certificate is approved to ensure the appointment is marked as Completed
     */
    public static function syncAppointmentStatus($appointmentId)
    {
        $appointment = Appointment::find($appointmentId);
        if ($appointment && $appointment->Status !== 'Completed') {
            $appointment->Status = 'Completed';
            $appointment->save();
            return true;
        }
        return false;
    }

    /**
     * Create a new report record
     */
    public static function createReport($data)
    {
        $reports = self::loadReports();
        
        $reportId = uniqid('rpt_');
        $reportNumber = self::generateReportNumber($data['type'] ?? 'WEEKLY');
        
        $report = [
            'id' => $reportId,
            'report_number' => $reportNumber,
            'type' => $data['type'] ?? 'WEEKLY',
            'week_number' => $data['week_number'] ?? date('W'),
            'year' => $data['year'] ?? date('Y'),
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'generated_by' => $data['generated_by'] ?? 'Admin',
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'anti_rabies_pdf' => null,
            'routine_services_pdf' => null,
            'summary' => $data['summary'] ?? [],
            'anti_rabies_count' => $data['anti_rabies_count'] ?? 0,
            'routine_services_count' => $data['routine_services_count'] ?? 0,
        ];
        
        $reports[$reportId] = $report;
        self::saveReports($reports);
        
        return $report;
    }

    /**
     * Get report by ID
     */
    public static function getReport($reportId)
    {
        $reports = self::loadReports();
        return $reports[$reportId] ?? null;
    }

    /**
     * Get all reports
     */
    public static function getAllReports()
    {
        $reports = self::loadReports();
        
        usort($reports, function($a, $b) {
            return strtotime($b['generated_at']) - strtotime($a['generated_at']);
        });
        
        return $reports;
    }

    /**
     * Delete report
     */
    public static function deleteReport($reportId)
    {
        $reports = self::loadReports();
        
        if (!isset($reports[$reportId])) {
            return false;
        }
        
        if (!empty($reports[$reportId]['anti_rabies_pdf'])) {
            $pdfFullPath = storage_path('app/public/' . $reports[$reportId]['anti_rabies_pdf']);
            if (file_exists($pdfFullPath)) {
                unlink($pdfFullPath);
            }
        }
        
        if (!empty($reports[$reportId]['routine_services_pdf'])) {
            $pdfFullPath = storage_path('app/public/' . $reports[$reportId]['routine_services_pdf']);
            if (file_exists($pdfFullPath)) {
                unlink($pdfFullPath);
            }
        }
        
        unset($reports[$reportId]);
        self::saveReports($reports);
        
        return true;
    }

    /**
     * Update report record
     */
    public static function updateReport($reportId, $data)
    {
        $reports = self::loadReports();
        
        if (!isset($reports[$reportId])) {
            return null;
        }
        
        foreach ($data as $key => $value) {
            $reports[$reportId][$key] = $value;
        }
        
        self::saveReports($reports);
        
        return $reports[$reportId];
    }

    /**
     * Generate Anti-Rabies Vaccination PDF
     */
    public static function generateAntiRabiesPdf($reportData)
    {
        $html = self::generateAntiRabiesHtml($reportData);
        
        $filename = 'reports/anti_rabies_' . $reportData['id'] . '.html';
        $fullPath = storage_path('app/public/' . $filename);
        
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($fullPath, $html);
        
        return $filename;
    }

    /**
     * Generate Routine Services PDF
     */
    public static function generateRoutineServicesPdf($reportData)
    {
        $html = self::generateRoutineServicesHtml($reportData);
        
        $filename = 'reports/routine_services_' . $reportData['id'] . '.html';
        $fullPath = storage_path('app/public/' . $filename);
        
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($fullPath, $html);
        
        return $filename;
    }

    /**
     * Generate Anti-Rabies Vaccination HTML
     */
    public static function generateAntiRabiesHtml($reportData)
    {
        $reportNumber = $reportData['report_number'] . '-AR';
        $startDate = Carbon::parse($reportData['start_date'])->format('F d, Y');
        $endDate = Carbon::parse($reportData['end_date'])->format('F d, Y');
        $weekNumber = $reportData['week_number'];
        $year = $reportData['year'];
        $generatedAt = Carbon::parse($reportData['generated_at'])->format('F d, Y h:i A');
        $generatedBy = htmlspecialchars($reportData['generated_by']);
        
        $antiRabiesData = $reportData['anti_rabies_data'] ?? [];

        $tableRows = '';
        $count = 0;
        foreach ($antiRabiesData as $index => $row) {
            $count++;
            $tableRows .= "<tr>
                <td class='border border-gray-400 px-3 py-2 text-center'>" . ($index + 1) . "</td>
                <td class='border border-gray-400 px-3 py-2'>" . htmlspecialchars($row['client_name']) . "</td>
                <td class='border border-gray-400 px-3 py-2'>" . htmlspecialchars($row['complete_address']) . "</td>
                <td class='border border-gray-400 px-3 py-2 text-center'>" . htmlspecialchars($row['civil_status']) . "</td>
                <td class='border border-gray-400 px-3 py-2 text-center'>" . htmlspecialchars($row['years_of_residency']) . "</td>
                <td class='border border-gray-400 px-3 py-2'>" . htmlspecialchars($row['pet_name']) . "</td>
                <td class='border border-gray-400 px-3 py-2 text-center'>" . htmlspecialchars($row['species']) . "</td>
                <td class='border border-gray-400 px-3 py-2 text-center'>" . htmlspecialchars($row['age']) . "</td>
                <td class='border border-gray-400 px-3 py-2 text-center'>" . htmlspecialchars($row['color']) . "</td>
                <td class='border border-gray-400 px-3 py-2 text-center'>" . htmlspecialchars($row['gender']) . "</td>
            </tr>";
        }

        if (empty($tableRows)) {
            $tableRows = "<tr><td colspan='10' class='border border-gray-400 px-3 py-6 text-center text-gray-500 italic'>No anti-rabies vaccination records for this period</td></tr>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anti-Rabies Vaccination Report - {$reportNumber}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { margin: 0; padding: 15px; }
            .no-print { display: none !important; }
            .report-container { box-shadow: none !important; }
        }
        @page { size: landscape; margin: 0.5in; }
        body { font-family: Arial, sans-serif; background: #f0f0f0; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="no-print bg-red-600 text-white p-4 text-center">
        <button onclick="window.print()" class="bg-white text-red-600 px-6 py-2 rounded-lg font-bold mr-4 hover:bg-red-50">
            🖨️ Print / Save as PDF
        </button>
        <button onclick="window.close()" class="bg-gray-800 text-white px-6 py-2 rounded-lg font-bold hover:bg-gray-900">
            ✕ Close
        </button>
        <p class="text-sm mt-2 text-red-100">Tip: Select "Save as PDF" and choose "Landscape" orientation</p>
    </div>
    
    <div class="report-container max-w-7xl mx-auto bg-white shadow-lg my-4 print:my-0 print:shadow-none">
        <div class="bg-red-700 text-white p-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                        <span class="text-red-700 font-bold text-3xl">🐾</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">CITY VETERINARY OFFICE</h1>
                        <p class="text-red-200">Anti-Rabies Vaccination Report (Walk-in)</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-red-200">Report No.</p>
                    <p class="font-bold text-xl">{$reportNumber}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-red-50 px-6 py-4 border-b border-red-200">
            <div class="grid grid-cols-4 gap-4 text-sm">
                <div><span class="text-gray-500">Week:</span><span class="font-bold ml-2">Week {$weekNumber}, {$year}</span></div>
                <div><span class="text-gray-500">Period:</span><span class="font-bold ml-2">{$startDate} - {$endDate}</span></div>
                <div><span class="text-gray-500">Generated:</span><span class="font-bold ml-2">{$generatedAt}</span></div>
                <div><span class="text-gray-500">By:</span><span class="font-bold ml-2">{$generatedBy}</span></div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-red-600 text-white">
                            <th class="border border-red-700 px-3 py-3 text-left">#</th>
                            <th class="border border-red-700 px-3 py-3 text-left">Client's Name</th>
                            <th class="border border-red-700 px-3 py-3 text-left">Complete Address</th>
                            <th class="border border-red-700 px-3 py-3 text-center">Civil Status</th>
                            <th class="border border-red-700 px-3 py-3 text-center">Years of Residency</th>
                            <th class="border border-red-700 px-3 py-3 text-left">Pet's Name</th>
                            <th class="border border-red-700 px-3 py-3 text-center">Species</th>
                            <th class="border border-red-700 px-3 py-3 text-center">Age</th>
                            <th class="border border-red-700 px-3 py-3 text-center">Color</th>
                            <th class="border border-red-700 px-3 py-3 text-center">Gender</th>
                        </tr>
                    </thead>
                    <tbody>{$tableRows}</tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-between items-center text-sm">
                <p class="text-gray-500">Total Records: <strong class="text-red-600">{$count}</strong></p>
                <p class="text-gray-400">City Veterinary Office - Anti-Rabies Vaccination Report</p>
            </div>
        </div>
        
        <div class="bg-gray-100 px-6 py-4 border-t text-center text-sm text-gray-500">
            <p>Generated on {$generatedAt} by {$generatedBy}</p>
            <p class="text-xs mt-1">This is a computer-generated report.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Generate Routine Services HTML
     */
    public static function generateRoutineServicesHtml($reportData)
    {
        $reportNumber = $reportData['report_number'] . '-RS';
        $startDate = Carbon::parse($reportData['start_date'])->format('F d, Y');
        $endDate = Carbon::parse($reportData['end_date'])->format('F d, Y');
        $weekNumber = $reportData['week_number'];
        $year = $reportData['year'];
        $generatedAt = Carbon::parse($reportData['generated_at'])->format('F d, Y h:i A');
        $generatedBy = htmlspecialchars($reportData['generated_by']);
        
        $routineServicesData = $reportData['routine_services_data'] ?? [];

        $tableRows = '';
        $count = 0;
        foreach ($routineServicesData as $index => $row) {
            $count++;
            $tableRows .= "<tr>
                <td class='border border-gray-400 px-3 py-2 text-center'>" . ($index + 1) . "</td>
                <td class='border border-gray-400 px-3 py-2'>" . htmlspecialchars($row['client_name']) . "</td>
                <td class='border border-gray-400 px-3 py-2'>" . htmlspecialchars($row['barangay']) . "</td>
                <td class='border border-gray-400 px-3 py-2 text-center'>" . htmlspecialchars($row['birthdate']) . "</td>
                <td class='border border-gray-400 px-3 py-2 text-center'>" . htmlspecialchars($row['contact_number']) . "</td>
                <td class='border border-gray-400 px-3 py-2'>" . htmlspecialchars($row['service_rendered']) . "</td>
                <td class='border border-gray-400 px-3 py-2'>" . htmlspecialchars($row['pet_name']) . "</td>
                <td class='border border-gray-400 px-3 py-2 text-center'>" . htmlspecialchars($row['species']) . "</td>
                <td class='border border-gray-400 px-3 py-2 text-center'>" . htmlspecialchars($row['gender']) . "</td>
            </tr>";
        }

        if (empty($tableRows)) {
            $tableRows = "<tr><td colspan='9' class='border border-gray-400 px-3 py-6 text-center text-gray-500 italic'>No routine service records for this period</td></tr>";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Routine Services Report - {$reportNumber}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body { margin: 0; padding: 15px; }
            .no-print { display: none !important; }
            .report-container { box-shadow: none !important; }
        }
        @page { size: landscape; margin: 0.5in; }
        body { font-family: Arial, sans-serif; background: #f0f0f0; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="no-print bg-green-600 text-white p-4 text-center">
        <button onclick="window.print()" class="bg-white text-green-600 px-6 py-2 rounded-lg font-bold mr-4 hover:bg-green-50">
            🖨️ Print / Save as PDF
        </button>
        <button onclick="window.close()" class="bg-gray-800 text-white px-6 py-2 rounded-lg font-bold hover:bg-gray-900">
            ✕ Close
        </button>
        <p class="text-sm mt-2 text-green-100">Tip: Select "Save as PDF" and choose "Landscape" orientation</p>
    </div>
    
    <div class="report-container max-w-7xl mx-auto bg-white shadow-lg my-4 print:my-0 print:shadow-none">
        <div class="bg-green-700 text-white p-6">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center">
                        <span class="text-green-700 font-bold text-3xl">🐾</span>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">CITY VETERINARY OFFICE</h1>
                        <p class="text-green-200">Routine Services Report</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-green-200">Report No.</p>
                    <p class="font-bold text-xl">{$reportNumber}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-green-50 px-6 py-4 border-b border-green-200">
            <div class="grid grid-cols-4 gap-4 text-sm">
                <div><span class="text-gray-500">Week:</span><span class="font-bold ml-2">Week {$weekNumber}, {$year}</span></div>
                <div><span class="text-gray-500">Period:</span><span class="font-bold ml-2">{$startDate} - {$endDate}</span></div>
                <div><span class="text-gray-500">Generated:</span><span class="font-bold ml-2">{$generatedAt}</span></div>
                <div><span class="text-gray-500">By:</span><span class="font-bold ml-2">{$generatedBy}</span></div>
            </div>
        </div>
        
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm border-collapse">
                    <thead>
                        <tr class="bg-green-600 text-white">
                            <th class="border border-green-700 px-3 py-3 text-left">#</th>
                            <th class="border border-green-700 px-3 py-3 text-left">Client's Name</th>
                            <th class="border border-green-700 px-3 py-3 text-left">Barangay</th>
                            <th class="border border-green-700 px-3 py-3 text-center">Birthdate</th>
                            <th class="border border-green-700 px-3 py-3 text-center">Contact Number</th>
                            <th class="border border-green-700 px-3 py-3 text-left">Service Rendered</th>
                            <th class="border border-green-700 px-3 py-3 text-left">Pet's Name</th>
                            <th class="border border-green-700 px-3 py-3 text-center">Species</th>
                            <th class="border border-green-700 px-3 py-3 text-center">Gender</th>
                        </tr>
                    </thead>
                    <tbody>{$tableRows}</tbody>
                </table>
            </div>
            <div class="mt-4 flex justify-between items-center text-sm">
                <p class="text-gray-500">Total Records: <strong class="text-green-600">{$count}</strong></p>
                <p class="text-gray-400">City Veterinary Office - Routine Services Report</p>
            </div>
        </div>
        
        <div class="bg-gray-100 px-6 py-4 border-t text-center text-sm text-gray-500">
            <p>Generated on {$generatedAt} by {$generatedBy}</p>
            <p class="text-xs mt-1">This is a computer-generated report.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}