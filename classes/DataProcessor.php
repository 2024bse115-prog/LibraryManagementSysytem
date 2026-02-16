<?php
/**
 * DataProcessor Class - Handles Data Processing Operations
 * 
 * WHY SEPARATE DATA PROCESSING CLASS?
 * - Separation of Concerns: Business logic separate from database operations
 * - Reusability: Processing functions can be used across different parts
 * - Testability: Can test data processing logic independently
 * - Maintainability: All data transformations in one place
 */
class DataProcessor {
    private $db;
    
    public function __construct($database) {
        $this->db = $database;
    }
    
    /**
     * Process and analyze resource statistics
     * WHY ASYNC PROCESSING?
     * - Heavy calculations don't block user requests
     * - Better user experience (non-blocking)
     * - Can process large datasets without timeout
     */
    public function processResourceStatistics($resourceId = null) {
        $conn = $this->db->getConnection();
        
        if ($resourceId) {
            // Process single resource
            $sql = "SELECT 
                        r.id,
                        r.title,
                        r.downloads,
                        r.views,
                        COUNT(DISTINCT r.id) as total_resources,
                        AVG(r.downloads) as avg_downloads,
                        AVG(r.views) as avg_views,
                        MAX(r.downloads) as max_downloads
                    FROM resources r
                    WHERE r.id = ?";
            
            $result = $this->db->query($sql, [$resourceId]);
            $data = $result->fetch_assoc();
            return $data ? $data : [];
        } else {
            // Process all resources for analytics
            $sql = "SELECT 
                        COUNT(*) as total_resources,
                        SUM(downloads) as total_downloads,
                        SUM(views) as total_views,
                        AVG(downloads) as avg_downloads,
                        AVG(views) as avg_views,
                        MAX(downloads) as most_downloaded,
                        MAX(views) as most_viewed,
                        COUNT(DISTINCT course_id) as courses_with_resources,
                        COUNT(DISTINCT uploader_id) as unique_uploaders
                    FROM resources";
            
            $result = $this->db->query($sql, []);
            $data = $result->fetch_assoc();
            return $data ? $data : [];
        }
    }
    
    /**
     * Process file metadata
     * WHY FILE PROCESSING?
     * - Extract file information (size, type, pages for PDFs)
     * - Validate file integrity
     * - Generate thumbnails/previews
     */
    public function processFileMetadata($filePath) {
        $metadata = [
            'exists' => file_exists($filePath),
            'size' => 0,
            'size_formatted' => '0 B',
            'type' => 'unknown',
            'last_modified' => null
        ];
        
        if ($metadata['exists']) {
            $metadata['size'] = filesize($filePath);
            $metadata['size_formatted'] = $this->formatFileSize($metadata['size']);
            $metadata['type'] = mime_content_type($filePath);
            $metadata['last_modified'] = date('Y-m-d H:i:s', filemtime($filePath));
            
            // For PDFs, could extract page count using libraries like FPDI
            if ($metadata['type'] === 'application/pdf') {
                $metadata['is_pdf'] = true;
                // Future: Extract PDF page count, title, author, etc.
            }
        }
        
        return $metadata;
    }
    
    /**
     * Format file size to human-readable format
     * WHY HELPER METHOD?
     * - Reusable formatting logic
     * - Consistent display across the application
     */
    private function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
    
    /**
     * Process search results with ranking
     * WHY RANKING ALGORITHM?
     * - Better search relevance
     * - Prioritize popular/important resources
     * - Improve user experience
     */
    public function rankSearchResults($results) {
        foreach ($results as &$result) {
            // Calculate relevance score
            $score = 0;
            
            // Downloads weight: 0.3
            $score += ($result['downloads'] ?? 0) * 0.3;
            
            // Views weight: 0.2
            $score += ($result['views'] ?? 0) * 0.2;
            
            // Recency weight: 0.5 (newer = higher score)
            $daysSinceUpload = (time() - strtotime($result['created_at'])) / 86400;
            $recencyScore = max(0, 100 - ($daysSinceUpload * 0.5));
            $score += $recencyScore * 0.5;
            
            $result['relevance_score'] = round($score, 2);
        }
        
        // Sort by relevance score
        usort($results, function($a, $b) {
            return $b['relevance_score'] <=> $a['relevance_score'];
        });
        
        return $results;
    }
    
    /**
     * Process user activity analytics
     * WHY ANALYTICS PROCESSING?
     * - Track user engagement
     * - Identify popular resources
     * - Generate insights for admins
     */
    public function processUserActivity($userId = null, $timeframe = '30 days') {
        $conn = $this->db->getConnection();
        
        $days = (int)explode(' ', $timeframe)[0];
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as uploads,
                    SUM(downloads) as total_downloads
                FROM resources
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)";
        
        $params = [$days];
        
        if ($userId) {
            $sql .= " AND uploader_id = ?";
            $params[] = $userId;
        }
        
        $sql .= " GROUP BY DATE(created_at) ORDER BY date DESC";
        
        $result = $this->db->query($sql, $params);
        $data = [];
        
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        return $data;
    }
    
    /**
     * Process and aggregate course statistics
     * WHY AGGREGATION?
     * - Reduce database queries
     * - Pre-compute statistics for faster display
     * - Support dashboard/analytics features
     */
    public function processCourseStatistics($courseId = null) {
        $conn = $this->db->getConnection();
        
        if ($courseId) {
            $sql = "SELECT 
                        c.id,
                        c.course_code,
                        c.course_name,
                        COUNT(r.id) as resource_count,
                        SUM(r.downloads) as total_downloads,
                        SUM(r.views) as total_views,
                        MAX(r.created_at) as last_upload
                    FROM courses c
                    LEFT JOIN resources r ON c.id = r.course_id
                    WHERE c.id = ?
                    GROUP BY c.id";
            
            $result = $this->db->query($sql, [$courseId]);
            $data = $result->fetch_assoc();
            return $data ? $data : [];
        } else {
            $sql = "SELECT 
                        c.id,
                        c.course_code,
                        c.course_name,
                        COUNT(r.id) as resource_count,
                        SUM(r.downloads) as total_downloads,
                        SUM(r.views) as total_views
                    FROM courses c
                    LEFT JOIN resources r ON c.id = r.course_id
                    GROUP BY c.id
                    ORDER BY resource_count DESC";
            
            $result = $this->db->query($sql, []);
            $data = [];
            
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            return $data;
        }
    }
}

