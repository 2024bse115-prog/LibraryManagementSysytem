<?php
/**
 * API Entry Point - RESTful API Handler
 * 
 * WHY THIS STRUCTURE?
 * - Single entry point for all API requests
 * - Uses OOP classes for better organization
 * - Handles all HTTP methods (GET, POST, PUT, DELETE)
 * - Returns JSON responses for frontend consumption
 */

require_once 'config.php';

// Initialize API
$api = new API($db, $dataProcessor);

// Route the request
$api->route();