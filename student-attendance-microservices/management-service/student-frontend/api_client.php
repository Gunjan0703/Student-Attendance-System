<?php
// api_client.php - PHP Client to communicate with Node.js backend

class StudentAPIClient {
    private $base_url;
    
    public function __construct($base_url = 'http://localhost:3001') {
        $this->base_url = rtrim($base_url, '/');
    }
    
    /**
     * Make HTTP request to Node.js API
     */
    private function makeRequest($endpoint, $method = 'GET', $data = null) {
        $url = $this->base_url . $endpoint;
        
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json'
            ),
        ));
        
        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        if (curl_error($curl)) {
            throw new Exception('Curl error: ' . curl_error($curl));
        }
        
        curl_close($curl);
        
        $decodedResponse = json_decode($response, true);
        
        return [
            'http_code' => $httpCode,
            'data' => $decodedResponse
        ];
    }
    
    /**
     * Get all students
     */
    public function getAllStudents() {
        try {
            $response = $this->makeRequest('/api/students', 'GET');
            return $response;
        } catch (Exception $e) {
            return [
                'http_code' => 500,
                'data' => [
                    'success' => false,
                    'message' => 'Connection error: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Get student by ID
     */
    public function getStudent($id) {
        try {
            $response = $this->makeRequest("/api/students/{$id}", 'GET');
            return $response;
        } catch (Exception $e) {
            return [
                'http_code' => 500,
                'data' => [
                    'success' => false,
                    'message' => 'Connection error: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Create new student
     */
    public function createStudent($studentData) {
        try {
            $response = $this->makeRequest('/api/students', 'POST', $studentData);
            return $response;
        } catch (Exception $e) {
            return [
                'http_code' => 500,
                'data' => [
                    'success' => false,
                    'message' => 'Connection error: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Update student
     */
    public function updateStudent($id, $studentData) {
        try {
            $response = $this->makeRequest("/api/students/{$id}", 'PUT', $studentData);
            return $response;
        } catch (Exception $e) {
            return [
                'http_code' => 500,
                'data' => [
                    'success' => false,
                    'message' => 'Connection error: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Delete student
     */
    public function deleteStudent($id) {
        try {
            $response = $this->makeRequest("/api/students/{$id}", 'DELETE');
            return $response;
        } catch (Exception $e) {
            return [
                'http_code' => 500,
                'data' => [
                    'success' => false,
                    'message' => 'Connection error: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Get students by class
     */
    public function getStudentsByClass($classId) {
        try {
            $response = $this->makeRequest("/api/classes/{$classId}/students", 'GET');
            return $response;
        } catch (Exception $e) {
            return [
                'http_code' => 500,
                'data' => [
                    'success' => false,
                    'message' => 'Connection error: ' . $e->getMessage()
                ]
            ];
        }
    }
    
    /**
     * Search students
     */
    public function searchStudents($query) {
        try {
            $response = $this->makeRequest("/api/students/search/" . urlencode($query), 'GET');
            return $response;
        } catch (Exception $e) {
            return [
                'http_code' => 500,
                'data' => [
                    'success' => false,
                    'message' => 'Connection error: ' . $e->getMessage()
                ]
            ];
        }
    }
}

// Usage example and helper functions

/**
 * Initialize API client
 */
function getApiClient() {
    return new StudentAPIClient('http://localhost:3001'); // Change to your Node.js service URL
}

/**
 * Handle API response and display messages
 */
function handleApiResponse($response, $successRedirect = null) {
    if ($response['data']['success']) {
        $_SESSION['success_message'] = $response['data']['message'];
        if ($successRedirect) {
            header("Location: $successRedirect");
            exit();
        }
        return true;
    } else {
        $_SESSION['error_message'] = $response['data']['message'];
        return false;
    }
}

/**
 * Display flash messages
 */
function displayFlashMessages() {
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
        unset($_SESSION['success_message']);
    }
    
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
}
?>
