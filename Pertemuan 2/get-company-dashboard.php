<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once '../config.php';
session_start();

function sendError($message, $statusCode = 400) {
    http_response_code($statusCode);
    echo json_encode(['success' => false, 'message' => $message]);
    exit;
}

// Check if user is logged in and is a company
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'company') {
    sendError('Unauthorized', 401);
}

try {
    $company_id = $_SESSION['user_id'];
    
    // Get company info
    $companyQuery = "SELECT id, name, email, phone, address, industry, description, logo_url FROM companies WHERE id = ?";
    $companyStmt = $conn->prepare($companyQuery);
    $companyStmt->bind_param('i', $company_id);
    $companyStmt->execute();
    $companyResult = $companyStmt->get_result();
    $company = $companyResult->fetch_assoc();
    $companyStmt->close();
    
    if (!$company) {
        sendError('Company not found', 404);
    }
    
    // Get active job postings count
    $activeQuery = "SELECT COUNT(*) as count FROM job_postings WHERE company_id = ? AND status = 'active'";
    $activeStmt = $conn->prepare($activeQuery);
    $activeStmt->bind_param('i', $company_id);
    $activeStmt->execute();
    $activeResult = $activeStmt->get_result();
    $activeCount = $activeResult->fetch_assoc()['count'];
    $activeStmt->close();
    
    // Get total applicants
    $applicantsQuery = "SELECT COUNT(*) as count FROM applicants a 
                        JOIN job_postings jp ON a.job_posting_id = jp.id 
                        WHERE jp.company_id = ?";
    $applicantsStmt = $conn->prepare($applicantsQuery);
    $applicantsStmt->bind_param('i', $company_id);
    $applicantsStmt->execute();
    $applicantsResult = $applicantsStmt->get_result();
    $totalApplicants = $applicantsResult->fetch_assoc()['count'];
    $applicantsStmt->close();
    
    // Get pending verifications
    $pendingQuery = "SELECT COUNT(*) as count FROM job_postings WHERE company_id = ? AND status = 'pending'";
    $pendingStmt = $conn->prepare($pendingQuery);
    $pendingStmt->bind_param('i', $company_id);
    $pendingStmt->execute();
    $pendingResult = $pendingStmt->get_result();
    $pendingCount = $pendingResult->fetch_assoc()['count'] ?? 0;
    $pendingStmt->close();
    
    // Get accepted applicants this month
    $acceptedQuery = "SELECT COUNT(*) as count FROM applicants a 
                      JOIN job_postings jp ON a.job_posting_id = jp.id 
                      WHERE jp.company_id = ? AND a.status = 'accepted' 
                      AND MONTH(a.applied_at) = MONTH(NOW()) AND YEAR(a.applied_at) = YEAR(NOW())";
    $acceptedStmt = $conn->prepare($acceptedQuery);
    $acceptedStmt->bind_param('i', $company_id);
    $acceptedStmt->execute();
    $acceptedResult = $acceptedStmt->get_result();
    $acceptedCount = $acceptedResult->fetch_assoc()['count'];
    $acceptedStmt->close();
    
    // Get applicants per position (last 7 days)
    $positionQuery = "SELECT jp.title, COUNT(a.id) as count 
                      FROM applicants a 
                      JOIN job_postings jp ON a.job_posting_id = jp.id 
                      WHERE jp.company_id = ? AND a.applied_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                      GROUP BY jp.id, jp.title 
                      ORDER BY count DESC";
    $positionStmt = $conn->prepare($positionQuery);
    $positionStmt->bind_param('i', $company_id);
    $positionStmt->execute();
    $positionResult = $positionStmt->get_result();
    $positions = [];
    while ($row = $positionResult->fetch_assoc()) {
        $positions[] = $row;
    }
    $positionStmt->close();
    
    // Get applicant status breakdown
    $statusQuery = "SELECT a.status, COUNT(a.id) as count 
                    FROM applicants a 
                    JOIN job_postings jp ON a.job_posting_id = jp.id 
                    WHERE jp.company_id = ? 
                    GROUP BY a.status";
    $statusStmt = $conn->prepare($statusQuery);
    $statusStmt->bind_param('i', $company_id);
    $statusStmt->execute();
    $statusResult = $statusStmt->get_result();
    $statusBreakdown = [];
    while ($row = $statusResult->fetch_assoc()) {
        $statusBreakdown[] = $row;
    }
    $statusStmt->close();
    
    // Get recent activities
    $activityQuery = "SELECT a.id, a.name, jp.title, a.applied_at, a.status 
                      FROM applicants a 
                      JOIN job_postings jp ON a.job_posting_id = jp.id 
                      WHERE jp.company_id = ? 
                      ORDER BY a.applied_at DESC LIMIT 10";
    $activityStmt = $conn->prepare($activityQuery);
    $activityStmt->bind_param('i', $company_id);
    $activityStmt->execute();
    $activityResult = $activityStmt->get_result();
    $activities = [];
    while ($row = $activityResult->fetch_assoc()) {
        $activities[] = $row;
    }
    $activityStmt->close();
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => [
            'company' => $company,
            'stats' => [
                'activeJobPostings' => $activeCount,
                'totalApplicants' => $totalApplicants,
                'pendingVerification' => $pendingCount,
                'acceptedThisMonth' => $acceptedCount
            ],
            'positions' => $positions,
            'statusBreakdown' => $statusBreakdown,
            'activities' => $activities
        ]
    ]);
    
    $conn->close();
} catch (Exception $e) {
    sendError('Error: ' . $e->getMessage(), 500);
}
?>
