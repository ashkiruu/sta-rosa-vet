<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Pet;
use App\Models\User;
use App\Models\Barangay;
use App\Models\Species;
use App\Models\Report;
use App\Models\ReportType;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ReportService
{
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
     * Generate report number
     */
    public static function generateReportNumber($type = 'WEEKLY')
    {
        $year = date('Y');
        $week = date('W');
        $count = Report::whereYear('created_at', $year)->count() + 1;
        return "RPT-{$type}-{$year}-W{$week}-" . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Get weekly date range
     */
    public static function getWeeklyDateRange($weekOffset = 0)
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
     */
    public static function getWeeklySummary($startDate, $endDate)
    {
        $totalAppointments = Appointment::whereBetween('Date', [$startDate, $endDate])->count();
        $completedAppointments = Appointment::whereBetween('Date', [$startDate, $endDate])
            ->where('Status', 'Completed')->count();
        $pendingAppointments = Appointment::whereBetween('Date', [$startDate, $endDate])
            ->where('Status', 'Pending')->count();
        $approvedAppointments = Appointment::whereBetween('Date', [$startDate, $endDate])
            ->where('Status', 'Approved')->count();

        $certificatesIssued = self::countCertificatesInPeriod($startDate, $endDate);
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
     */
    private static function countCertificatesInPeriod($startDate, $endDate)
    {
        $certificates = CertificateService::getAllCertificates('approved');
        $count = 0;
        
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();
        
        foreach ($certificates as $cert) {
            if (!empty($cert['vaccination_date'])) {
                $certDate = Carbon::parse($cert['vaccination_date']);
                if ($certDate->between($start, $end)) {
                    $count++;
                    continue;
                }
            }
            
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
     * Create a new report record (using database)
     */
    public static function createReport($data)
    {
        $reportNumber = self::generateReportNumber($data['type'] ?? 'WEEKLY');
        
        // Get or create report type
        $reportTypeId = $data['report_type_id'] ?? null;
        
        if (!$reportTypeId) {
            // Default to creating both reports, use Anti-Rabies as primary
            $reportType = ReportType::first();
            $reportTypeId = $reportType ? $reportType->ReportType_ID : 1;
        }
        
        $report = Report::create([
            'Report_Number' => $reportNumber,
            'ReportType_ID' => $reportTypeId,
            'Week_Number' => $data['week_number'] ?? date('W'),
            'Year' => $data['year'] ?? date('Y'),
            'Start_Date' => $data['start_date'],
            'End_Date' => $data['end_date'],
            'Generated_By' => $data['generated_by'] ?? 'Admin',
            'Generated_At' => now(),
            'File_Path' => null,
            'Record_Count' => $data['record_count'] ?? 0,
            'Summary' => $data['summary'] ?? [],
        ]);
        
        // Convert to array format for backward compatibility
        return self::reportToArray($report);
    }

    /**
     * Create both Anti-Rabies and Routine Services reports
     */
    public static function createWeeklyReports($data)
    {
        $reports = [];
        
        // Create Anti-Rabies Report
        $antiRabiesType = ReportType::antiRabies();
        if ($antiRabiesType) {
            $arReport = Report::create([
                'Report_Number' => self::generateReportNumber('AR'),
                'ReportType_ID' => $antiRabiesType->ReportType_ID,
                'Week_Number' => $data['week_number'] ?? date('W'),
                'Year' => $data['year'] ?? date('Y'),
                'Start_Date' => $data['start_date'],
                'End_Date' => $data['end_date'],
                'Generated_By' => $data['generated_by'] ?? 'Admin',
                'Generated_At' => now(),
                'File_Path' => null,
                'Record_Count' => $data['anti_rabies_count'] ?? 0,
                'Summary' => $data['summary'] ?? [],
            ]);
            $reports['anti_rabies'] = $arReport;
        }
        
        // Create Routine Services Report
        $routineType = ReportType::routineServices();
        if ($routineType) {
            $rsReport = Report::create([
                'Report_Number' => self::generateReportNumber('RS'),
                'ReportType_ID' => $routineType->ReportType_ID,
                'Week_Number' => $data['week_number'] ?? date('W'),
                'Year' => $data['year'] ?? date('Y'),
                'Start_Date' => $data['start_date'],
                'End_Date' => $data['end_date'],
                'Generated_By' => $data['generated_by'] ?? 'Admin',
                'Generated_At' => now(),
                'File_Path' => null,
                'Record_Count' => $data['routine_services_count'] ?? 0,
                'Summary' => $data['summary'] ?? [],
            ]);
            $reports['routine_services'] = $rsReport;
        }
        
        return $reports;
    }

    /**
     * Get report by ID (from database)
     */
    public static function getReport($reportId)
    {
        $report = Report::with('reportType')->find($reportId);
        
        if (!$report) {
            return null;
        }
        
        return self::reportToArray($report);
    }

    /**
     * Get all reports (from database)
     */
    public static function getAllReports()
    {
        $reports = Report::with('reportType')
            ->orderBy('Generated_At', 'desc')
            ->get();
        
        // Group reports by week/year for display
        $groupedReports = [];
        
        foreach ($reports as $report) {
            $key = $report->Year . '-' . $report->Week_Number . '-' . $report->Start_Date->format('Y-m-d');
            
            if (!isset($groupedReports[$key])) {
                $groupedReports[$key] = [
                    'id' => $report->Report_ID,
                    'report_number' => preg_replace('/-(AR|RS)$/', '', $report->Report_Number),
                    'type' => 'WEEKLY',
                    'week_number' => $report->Week_Number,
                    'year' => $report->Year,
                    'start_date' => $report->Start_Date->format('Y-m-d'),
                    'end_date' => $report->End_Date->format('Y-m-d'),
                    'generated_by' => $report->Generated_By,
                    'generated_at' => $report->Generated_At->format('Y-m-d H:i:s'),
                    'summary' => $report->Summary,
                    'anti_rabies_id' => null,
                    'anti_rabies_pdf' => null,
                    'anti_rabies_count' => 0,
                    'routine_services_id' => null,
                    'routine_services_pdf' => null,
                    'routine_services_count' => 0,
                ];
            }
            
            // Add report type specific data
            if ($report->isAntiRabies()) {
                $groupedReports[$key]['anti_rabies_id'] = $report->Report_ID;
                $groupedReports[$key]['anti_rabies_pdf'] = $report->File_Path;
                $groupedReports[$key]['anti_rabies_count'] = $report->Record_Count;
            } elseif ($report->isRoutineServices()) {
                $groupedReports[$key]['routine_services_id'] = $report->Report_ID;
                $groupedReports[$key]['routine_services_pdf'] = $report->File_Path;
                $groupedReports[$key]['routine_services_count'] = $report->Record_Count;
            }
        }
        
        return array_values($groupedReports);
    }

    /**
     * Get reports by type
     */
    public static function getReportsByType($reportTypeId)
    {
        return Report::with('reportType')
            ->where('ReportType_ID', $reportTypeId)
            ->orderBy('Generated_At', 'desc')
            ->get()
            ->map(fn($report) => self::reportToArray($report))
            ->toArray();
    }

    /**
     * Delete report (from database)
     */
    public static function deleteReport($reportId)
    {
        // If it's a grouped report ID, delete both associated reports
        $report = Report::find($reportId);
        
        if (!$report) {
            return false;
        }
        
        // Delete all reports for the same week/year/date range
        Report::where('Week_Number', $report->Week_Number)
            ->where('Year', $report->Year)
            ->where('Start_Date', $report->Start_Date)
            ->where('End_Date', $report->End_Date)
            ->each(function ($r) {
                if ($r->File_Path && file_exists(storage_path('app/public/' . $r->File_Path))) {
                    unlink(storage_path('app/public/' . $r->File_Path));
                }
                $r->delete();
            });
        
        return true;
    }

    /**
     * Delete single report by ID
     */
    public static function deleteSingleReport($reportId)
    {
        $report = Report::find($reportId);
        
        if (!$report) {
            return false;
        }
        
        // Delete file if exists
        if ($report->File_Path && file_exists(storage_path('app/public/' . $report->File_Path))) {
            unlink(storage_path('app/public/' . $report->File_Path));
        }
        
        $report->delete();
        
        return true;
    }

    /**
     * Update report record (in database)
     */
    public static function updateReport($reportId, $data)
    {
        $report = Report::find($reportId);
        
        if (!$report) {
            return null;
        }
        
        // Map old field names to new ones if needed
        $updateData = [];
        
        if (isset($data['anti_rabies_pdf'])) {
            if ($report->isAntiRabies()) {
                $updateData['File_Path'] = $data['anti_rabies_pdf'];
            }
        }
        
        if (isset($data['routine_services_pdf'])) {
            if ($report->isRoutineServices()) {
                $updateData['File_Path'] = $data['routine_services_pdf'];
            }
        }
        
        if (isset($data['File_Path'])) {
            $updateData['File_Path'] = $data['File_Path'];
        }
        
        if (isset($data['Record_Count'])) {
            $updateData['Record_Count'] = $data['Record_Count'];
        }
        
        if (isset($data['Summary'])) {
            $updateData['Summary'] = $data['Summary'];
        }
        
        if (!empty($updateData)) {
            $report->update($updateData);
        }
        
        return self::reportToArray($report->fresh());
    }

    /**
     * Convert Report model to array format for backward compatibility
     */
    private static function reportToArray(Report $report): array
    {
        return [
            'id' => $report->Report_ID,
            'report_number' => $report->Report_Number,
            'report_type_id' => $report->ReportType_ID,
            'report_type_name' => $report->reportType->Report_Name ?? 'Unknown',
            'type' => 'WEEKLY',
            'week_number' => $report->Week_Number,
            'year' => $report->Year,
            'start_date' => $report->Start_Date->format('Y-m-d'),
            'end_date' => $report->End_Date->format('Y-m-d'),
            'generated_by' => $report->Generated_By,
            'generated_at' => $report->Generated_At->format('Y-m-d H:i:s'),
            'file_path' => $report->File_Path,
            'record_count' => $report->Record_Count,
            'summary' => $report->Summary,
            // Legacy fields for backward compatibility
            'anti_rabies_pdf' => $report->isAntiRabies() ? $report->File_Path : null,
            'routine_services_pdf' => $report->isRoutineServices() ? $report->File_Path : null,
            'anti_rabies_count' => $report->isAntiRabies() ? $report->Record_Count : 0,
            'routine_services_count' => $report->isRoutineServices() ? $report->Record_Count : 0,
        ];
    }

    /**
     * Generate Anti-Rabies Vaccination PDF
     */
    public static function generateAntiRabiesPdf($reportData)
    {
        $html = self::generateAntiRabiesHtml($reportData);
        
        $reportId = $reportData['id'] ?? $reportData['anti_rabies_id'] ?? uniqid();
        $filename = 'reports/anti_rabies_' . $reportId . '.html';
        $fullPath = storage_path('app/public/' . $filename);
        
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($fullPath, $html);
        
        // Update the report record with the file path
        if (isset($reportData['anti_rabies_id'])) {
            Report::where('Report_ID', $reportData['anti_rabies_id'])
                ->update(['File_Path' => $filename]);
        }
        
        return $filename;
    }

    /**
     * Generate Routine Services PDF
     */
    public static function generateRoutineServicesPdf($reportData)
    {
        $html = self::generateRoutineServicesHtml($reportData);
        
        $reportId = $reportData['id'] ?? $reportData['routine_services_id'] ?? uniqid();
        $filename = 'reports/routine_services_' . $reportId . '.html';
        $fullPath = storage_path('app/public/' . $filename);
        
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        file_put_contents($fullPath, $html);
        
        // Update the report record with the file path
        if (isset($reportData['routine_services_id'])) {
            Report::where('Report_ID', $reportData['routine_services_id'])
                ->update(['File_Path' => $filename]);
        }
        
        return $filename;
    }

    /**
     * Generate Anti-Rabies Vaccination HTML
     */
    public static function generateAntiRabiesHtml($reportData)
    {
        $reportNumber = ($reportData['report_number'] ?? 'RPT-WEEKLY') . '-AR';
        $startDate = Carbon::parse($reportData['start_date'])->format('F d, Y');
        $endDate = Carbon::parse($reportData['end_date'])->format('F d, Y');
        $weekNumber = $reportData['week_number'];
        $year = $reportData['year'];
        $generatedAt = isset($reportData['generated_at']) 
            ? Carbon::parse($reportData['generated_at'])->format('F d, Y h:i A')
            : now()->format('F d, Y h:i A');
        $generatedBy = htmlspecialchars($reportData['generated_by'] ?? 'Admin');
        
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
        $reportNumber = ($reportData['report_number'] ?? 'RPT-WEEKLY') . '-RS';
        $startDate = Carbon::parse($reportData['start_date'])->format('F d, Y');
        $endDate = Carbon::parse($reportData['end_date'])->format('F d, Y');
        $weekNumber = $reportData['week_number'];
        $year = $reportData['year'];
        $generatedAt = isset($reportData['generated_at']) 
            ? Carbon::parse($reportData['generated_at'])->format('F d, Y h:i A')
            : now()->format('F d, Y h:i A');
        $generatedBy = htmlspecialchars($reportData['generated_by'] ?? 'Admin');
        
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