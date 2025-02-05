<?php
require_once '../config.php';
require_once '../classes/Database.php';
require_once '../classes/Issue.php';

header('Content-Type: application/json');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['issueId']) || !isset($input['status'])) {
        throw new Exception('Missing required parameters');
    }

    $db = Database::getInstance($config)->getConnection();
    $issueModel = new Issue($db);
    
    $success = $issueModel->updateIssueStatus($input['issueId'], $input['status']);
    
    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Failed to update issue status');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
