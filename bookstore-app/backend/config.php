<?php
/**
 * config.php - Encryption Configuration File
 * 
 * PURPOSE: Centralized encryption settings for AES-256-GCM phone number encryption
 * - Defines encryption algorithm, key, and parameters
 * - Used by encryption.php for encrypting/decrypting sensitive data
 * - Supports environment variable for production key management
 * 
 * SECURITY NOTE:
 * - In production, store ENCRYPTION_KEY in environment variables or secure vault
 * - Never hardcode encryption keys in source files
 * - Current development key must be changed before going to production
 */

/**
 * ENCRYPTION KEY MANAGEMENT
 * 
 * Two approaches:
 * 1. Environment variable (recommended for production)
 * 2. Hardcoded key (development only)
 */

// Use getenv('ENCRYPTION_KEY') to retrieve environment variable from system
// Environment variables are set in .env files or server configuration
// More secure than hardcoding keys in source files
if (getenv('ENCRYPTION_KEY')) {
    // Use hex2bin() to convert hexadecimal string to binary format
    // This converts a 64-character hex string to 32 bytes
    // For example: "a1b2c3..." → binary blob of 32 bytes
    $key = hex2bin(getenv('ENCRYPTION_KEY'));
    
    // Use === false comparison to check if hex2bin() conversion failed
    // Use strlen() to check the length of converted key string
    // Must be exactly 32 bytes (256 bits) for AES-256
    if ($key === false || strlen($key) !== 32) {
        // Use throw new Exception to indicate configuration error
        throw new Exception('Invalid ENCRYPTION_KEY in environment. Must be 64-char hex string.');
    }
    
    // Use define() function to create constant for encryption key
    define('ENCRYPTION_KEY', $key);
} else {
    // Development fallback - CHANGE THIS IN PRODUCTION!
    // To generate a secure key, run: openssl rand -hex 32
    // This creates a SHA-256 hash which is 32 bytes (256 bits)
    // Use hash('sha256', $string, true) to create a 32-byte hash
    // Second parameter 'sha256' specifies the hashing algorithm
    // Third parameter true returns binary format (not hex)
    define('ENCRYPTION_KEY', hash('sha256', 'bookstore-app-development-key-change-in-production', true));
}

/**
 * ENCRYPTION ALGORITHM CONFIGURATION
 * Using AES-256-GCM (Advanced Encryption Standard with Galois/Counter Mode)
 */

// AES-256 in GCM mode with OpenSSL
// GCM provides both encryption AND authentication (detects tampering)
define('ENCRYPTION_CIPHER', 'aes-256-gcm');

// Initialization Vector (IV) length: 12 bytes (96 bits)
// GCM recommends 96-bit IVs for performance and security
define('ENCRYPTION_IV_LENGTH', 12);

// Authentication Tag length: 16 bytes (128 bits)
// Verifies data hasn't been tampered with
define('ENCRYPTION_TAG_LENGTH', 16);

/**
 * ENCRYPTION PARAMETERS SUMMARY:
 * - Algorithm: AES-256-GCM
 * - Key size: 256 bits (32 bytes)
 * - IV size: 96 bits (12 bytes)  
 * - Tag size: 128 bits (16 bits)
 * - Protection: Encryption + Authentication
 */
?>
