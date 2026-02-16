<?php
/**
 * API Class - RESTful API Handler
 * 
 * WHY RESTful API?
 * - Standardized: Follows HTTP methods (GET, POST, PUT, DELETE)
 * - Stateless: Each request contains all information needed
 * - Scalable: Can be consumed by web, mobile, or other services
 * - Separation: Frontend and backend can be developed independently
 * - JSON Format: Lightweight, easy to parse, universal support
 */
class API {
    private $db;
    private $dataProcessor;
    
    public function __construct($database, $dataProcessor) {
        $this->db = $database;
        $this->dataProcessor = $dataProcessor;
        $this->handleCORS();
    }
    
    /**
     * Handle CORS (Cross-Origin Resource Sharing)
     * WHY CORS?
     * - Allows frontend on different domain/port to access API
     * - Required for modern web applications
     * - Security: Controls which origins can access the API
     */
    private function handleCORS() {
        header('Access-Control-Allow-Origin: *'); // In production, specify actual domain
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }
    
    /**
     * Main API router
     * WHY ROUTER PATTERN?
     * - Single entry point for all API requests
     * - Clean URL structure (/api.php?endpoint=resources)
     * - Easy to add new endpoints
     */
    public function route() {
        $method = $_SERVER['REQUEST_METHOD'];
        $endpoint = $_GET['endpoint'] ?? 'resources';
        $id = $_GET['id'] ?? null;
        
        try {
            switch ($endpoint) {
                case 'resources':
                    $this->handleResources($method, $id);
                    break;
                case 'search':
                    $this->handleSearch();
                    break;
                case 'courses':
                    $this->handleCourses($id);
                    break;
                case 'faculties':
                    $this->handleFaculties();
                    break;
                case 'statistics':
                    $this->handleStatistics();
                    break;
                default:
                    $this->sendResponse(['error' => 'Endpoint not found'], 404);
            }
        } catch (Exception $e) {
            $this->sendResponse(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Handle resources endpoint
     * WHY SEPARATE METHOD?
     * - Clean code organization
     * - Each endpoint has its own logic
     * - Easy to maintain and test
     */
    private function handleResources($method, $id) {
        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->getResource($id);
                } else {
                    $this->getResources();
                }
                break;
            case 'POST':
                $this->createResource();
                break;
            case 'PUT':
                $this->updateResource($id);
                break;
            case 'DELETE':
                $this->deleteResource($id);
                break;
            default:
                $this->sendResponse(['error' => 'Method not allowed'], 405);
        }
    }
    
    /**
     * Get all resources with filters
     * WHY ASYNC-FRIENDLY DESIGN?
     * - Returns JSON immediately (non-blocking)
     * - Frontend can process data asynchronously
     * - Better user experience (no page reload)
     */
    private function getResources() {
        $search = $_GET['q'] ?? '';
        $faculty = $_GET['faculty'] ?? '';
        $year = $_GET['year'] ?? '';
        $semester = $_GET['semester'] ?? '';
        $sort = $_GET['sort'] ?? 'recent';
        $limit = $_GET['limit'] ?? 50;
        $offset = $_GET['offset'] ?? 0;
        
        // Build query
        $sql = "SELECT r.*, c.course_code, c.course_name, f.name as faculty_name 
                FROM resources r 
                JOIN courses c ON r.course_id = c.id 
                JOIN faculties f ON c.faculty_id = f.id 
                WHERE 1=1";
        
        $params = [];
        
        if ($search) {
            $sql .= " AND (c.course_code LIKE ? OR c.course_name LIKE ? OR r.title LIKE ? OR r.description LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        }
        
        if ($faculty) {
            $sql .= " AND c.faculty_id = ?";
            $params[] = $faculty;
        }
        
        if ($year) {
            $sql .= " AND r.year = ?";
            $params[] = $year;
        }
        
        if ($semester) {
            $sql .= " AND r.semester = ?";
            $params[] = $semester;
        }
        
        // Sorting
        switch($sort) {
            case 'downloads': $sql .= " ORDER BY r.downloads DESC"; break;
            case 'views': $sql .= " ORDER BY r.views DESC"; break;
            case 'oldest': $sql .= " ORDER BY r.created_at ASC"; break;
            default: $sql .= " ORDER BY r.created_at DESC";
        }
        
        // Pagination
        $sql .= " LIMIT ? OFFSET ?";
        $params[] = (int)$limit;
        $params[] = (int)$offset;
        
        $result = $this->db->query($sql, $params);
        $resources = [];
        
        while ($row = $result->fetch_assoc()) {
            // Process file metadata
            if (isset($row['file_path'])) {
                $row['file_metadata'] = $this->dataProcessor->processFileMetadata($row['file_path']);
            }
            $resources[] = $row;
        }
        
        // Apply ranking if search query exists
        if ($search) {
            $resources = $this->dataProcessor->rankSearchResults($resources);
        }
        
        $this->sendResponse([
            'success' => true,
            'data' => $resources,
            'count' => count($resources)
        ]);
    }
    
    /**
     * Get single resource
     */
    private function getResource($id) {
        $sql = "SELECT r.*, c.course_code, c.course_name, f.name as faculty_name 
                FROM resources r 
                JOIN courses c ON r.course_id = c.id 
                JOIN faculties f ON c.faculty_id = f.id 
                WHERE r.id = ?";
        
        $result = $this->db->query($sql, [$id]);
        $resource = $result->fetch_assoc();
        
        if ($resource) {
            // Process file metadata
            if (isset($resource['file_path'])) {
                $resource['file_metadata'] = $this->dataProcessor->processFileMetadata($resource['file_path']);
            }
            
            // Increment view count (async operation)
            $this->incrementViews($id);
            
            $this->sendResponse(['success' => true, 'data' => $resource]);
        } else {
            $this->sendResponse(['error' => 'Resource not found'], 404);
        }
    }
    
    /**
     * Handle search endpoint
     * WHY DEDICATED SEARCH ENDPOINT?
     * - Specialized search logic
     * - Can add advanced features (full-text search, fuzzy matching)
     * - Better performance with indexing
     */
    private function handleSearch() {
        $query = $_GET['q'] ?? '';
        
        if (empty($query)) {
            $this->sendResponse(['error' => 'Search query required'], 400);
            return;
        }
        
        // Use getResources but with search-specific processing
        $this->getResources();
    }
    
    /**
     * Handle courses endpoint
     */
    private function handleCourses($id) {
        if ($id) {
            $sql = "SELECT c.*, f.name as faculty_name,
                    (SELECT COUNT(*) FROM resources WHERE course_id = c.id) as resource_count
                    FROM courses c
                    JOIN faculties f ON c.faculty_id = f.id
                    WHERE c.id = ?";
            
            $result = $this->db->query($sql, [$id]);
            $course = $result->fetch_assoc();
            
            if ($course) {
                // Add statistics
                $course['statistics'] = $this->dataProcessor->processCourseStatistics($id);
                $this->sendResponse(['success' => true, 'data' => $course]);
            } else {
                $this->sendResponse(['error' => 'Course not found'], 404);
            }
        } else {
            $facultyId = $_GET['faculty_id'] ?? '';
            $sql = "SELECT c.*, f.name as faculty_name,
                    (SELECT COUNT(*) FROM resources WHERE course_id = c.id) as resource_count
                    FROM courses c
                    JOIN faculties f ON c.faculty_id = f.id";
            
            $params = [];
            if ($facultyId) {
                $sql .= " WHERE c.faculty_id = ?";
                $params[] = $facultyId;
            }
            
            $sql .= " ORDER BY c.course_name";
            
            $result = $this->db->query($sql, $params);
            $courses = [];
            
            while ($row = $result->fetch_assoc()) {
                $courses[] = $row;
            }
            
            $this->sendResponse(['success' => true, 'data' => $courses]);
        }
    }
    
    /**
     * Handle faculties endpoint
     */
    private function handleFaculties() {
        $sql = "SELECT f.*, 
                (SELECT COUNT(*) FROM courses WHERE faculty_id = f.id) as course_count,
                (SELECT COUNT(*) FROM resources r 
                 JOIN courses c ON r.course_id = c.id 
                 WHERE c.faculty_id = f.id) as resource_count
                FROM faculties f
                ORDER BY f.name";
        
        $result = $this->db->query($sql);
        $faculties = [];
        
        while ($row = $result->fetch_assoc()) {
            $faculties[] = $row;
        }
        
        $this->sendResponse(['success' => true, 'data' => $faculties]);
    }
    
    /**
     * Handle statistics endpoint
     * WHY STATISTICS API?
     * - Dashboard data
     * - Analytics for admins
     * - Real-time insights
     */
    private function handleStatistics() {
        $type = $_GET['type'] ?? 'general';
        
        switch ($type) {
            case 'resources':
                $stats = $this->dataProcessor->processResourceStatistics();
                break;
            case 'courses':
                $stats = $this->dataProcessor->processCourseStatistics();
                break;
            case 'activity':
                $userId = $_GET['user_id'] ?? null;
                $timeframe = $_GET['timeframe'] ?? '30 days';
                $stats = $this->dataProcessor->processUserActivity($userId, $timeframe);
                break;
            default:
                // General statistics
                $stats = [
                    'resources' => $this->dataProcessor->processResourceStatistics(),
                    'courses' => $this->dataProcessor->processCourseStatistics()
                ];
        }
        
        $this->sendResponse(['success' => true, 'data' => $stats]);
    }
    
    /**
     * Increment view count (async-friendly)
     * WHY ASYNC OPERATION?
     * - Non-blocking: Doesn't slow down the main response
     * - Better UX: User gets data immediately
     * - Can be queued for batch processing
     */
    private function incrementViews($resourceId) {
        // This could be made truly async with a job queue
        $sql = "UPDATE resources SET views = views + 1 WHERE id = ?";
        $this->db->query($sql, [$resourceId]);
    }
    
    /**
     * Create resource (POST)
     */
    private function createResource() {
        // Implementation for creating resources via API
        $this->sendResponse(['error' => 'Not implemented'], 501);
    }
    
    /**
     * Update resource (PUT)
     */
    private function updateResource($id) {
        // Implementation for updating resources via API
        $this->sendResponse(['error' => 'Not implemented'], 501);
    }
    
    /**
     * Delete resource (DELETE)
     */
    private function deleteResource($id) {
        // Implementation for deleting resources via API
        $this->sendResponse(['error' => 'Not implemented'], 501);
    }
    
    /**
     * Send JSON response
     * WHY UNIFIED RESPONSE METHOD?
     * - Consistent API response format
     * - Easy to add logging, error handling
     * - Centralized response formatting
     */
    private function sendResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit();
    }
}

