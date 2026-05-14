<?php
/**
 * encryption.php - Encryption Utility Class
 * 
 * PURPOSE: Handles AES-256-GCM encryption/decryption of sensitive data
 * - Encrypts phone numbers before database storage
 * - Decrypts phone numbers when needed for display/verification
 * - Provides separate methods for raw and database storage formats
 * - Uses authenticated encryption (detects data tampering)
 * 
 * HOW IT WORKS:
 * 1. Generates random IV (initialization vector) for each encryption
 * 2. Encrypts data using AES-256-GCM algorithm
 * 3. Generates authentication tag to detect tampering
 * 4. Can optionally base64-encode for safe database storage
 */

// Include encryption configuration settings
require_once 'config.php';

class EncryptionUtil {

    /**
     * ENCRYPT METHOD - Core encryption function
     * 
     * PURPOSE: Encrypts plaintext data using AES-256-GCM
     * - Generates random IV for each encryption
     * - Returns encrypted data, IV, and authentication tag
     * 
     * @param string $plaintext The text to encrypt (e.g., phone number)
     * @return array Associative array with keys: 'encrypted', 'iv', 'tag'
     * @throws Exception if encryption fails
     * 
     * HOW IT WORKS:
     * 1. Get the encryption key from config
     * 2. Generate random 12-byte IV (changes each time)
     * 3. Call openssl_encrypt with AES-256-GCM
     * 4. Receive encrypted ciphertext and authentication tag
     * 5. Return all three components (needed for decryption)
     */
    public static function encrypt($plaintext) {
        // Get encryption key from config.php (32-byte hexadecimal key)
        $key = ENCRYPTION_KEY;
        
        // Use random_bytes(12) to generate random Initialization Vector (IV)
        // Each encryption gets a new unique IV for better security
        // 12-byte (96-bit) IV is standard for GCM mode
        $iv = random_bytes(ENCRYPTION_IV_LENGTH);
        
        // Initialize empty string for authentication tag
        // This variable will be filled by openssl_encrypt() using pass-by-reference
        $tag = '';

        // Use openssl_encrypt() to perform AES-256-GCM encryption
        // Parameters:
        // - $plaintext: Data to encrypt (e.g., "555-1234567")
        // - ENCRYPTION_CIPHER: Algorithm constant 'aes-256-gcm'
        // - $key: 32-byte encryption key (hex converted to binary)
        // - OPENSSL_RAW_DATA: Flag to return raw binary data (not base64)
        // - $iv: Initialization vector (by reference, required for GCM)
        // - $tag: Authentication tag (by reference, filled by function)
        // - '': Additional authenticated data (empty for phone number)
        // - ENCRYPTION_TAG_LENGTH: Tag length constant 16 (128 bits)
        $encrypted = openssl_encrypt(
            $plaintext,              // Data to encrypt
            ENCRYPTION_CIPHER,       // Algorithm: 'aes-256-gcm'
            $key,                    // 32-byte encryption key
            OPENSSL_RAW_DATA,        // Return raw binary data (not base64)
            $iv,                     // Initialization vector
            $tag,                    // Authentication tag (modified by reference)
            '',                      // Additional authenticated data (empty)
            ENCRYPTION_TAG_LENGTH    // Tag length: 16 bytes (128 bits)
        );

        // Check if encryption succeeded using === false comparison
        // openssl_encrypt() returns false on failure
        if ($encrypted === false) {
            // Use throw new Exception to raise error with oppenssl error message
            throw new Exception('Encryption failed: ' . openssl_error_string());
        }

        // Use array literal syntax [] to create return array
        // Use => to associate keys with values
        return [
            'encrypted' => $encrypted,  // Raw binary encrypted ciphertext
            'iv' => $iv,                // Raw binary initialization vector
            'tag' => $tag               // Raw binary authentication tag
        ];
    }

    /**
     * DECRYPT METHOD - Core decryption function
     * 
     * PURPOSE: Decrypts AES-256-GCM encrypted data
     * - Verifies authentication tag (detects tampering)
     * - Returns plaintext
     * 
     * @param string $encrypted Raw binary encrypted data
     * @param string $iv Raw binary initialization vector
     * @param string $tag Raw binary authentication tag
     * @return string The decrypted plaintext
     * @throws Exception if decryption fails or data is tampered
     * 
     * HOW IT WORKS:
     * 1. Get encryption key from config
     * 2. Call openssl_decrypt with AES-256-GCM
     * 3. Provide encrypted data, IV, and tag
     * 4. If tag verification fails, throw exception (data was tampered with)
     * 5. Return decrypted plaintext
     */
    public static function decrypt($encrypted, $iv, $tag) {
        // Get encryption key from config.php (32-byte key)
        $key = ENCRYPTION_KEY;

        // Use openssl_decrypt() to perform AES-256-GCM decryption
        // Parameters:
        // - $encrypted: Raw binary encrypted ciphertext
        // - ENCRYPTION_CIPHER: Algorithm 'aes-256-gcm'
        // - $key: 32-byte encryption key (same key used during encryption)
        // - OPENSSL_RAW_DATA: Input is raw binary data (not base64)
        // - $iv: Initialization vector (same IV used during encryption)
        // - $tag: Authentication tag (verified during decryption)
        $decrypted = openssl_decrypt(
            $encrypted,              // Encrypted ciphertext
            ENCRYPTION_CIPHER,       // Algorithm: 'aes-256-gcm'
            $key,                    // 32-byte encryption key
            OPENSSL_RAW_DATA,        // Input is raw binary data
            $iv,                     // Initialization vector
            $tag                     // Authentication tag (verified)
        );

        // Check if decryption succeeded using === false comparison
        // Returns false if decryption fails or tag verification fails
        if ($decrypted === false) {
            // Use throw new Exception to indicate decryption/tampering error
            throw new Exception('Decryption failed or data tampered: ' . openssl_error_string());
        }

        // Return plaintext - decryption and tag verification successful
        return $decrypted;
    }

    /**
     * ENCRYPTFORSTORAGE METHOD - Encryption with database formatting
     * 
     * PURPOSE: Encrypts data and formats for safe storage in database
     * - Calls encrypt() to get raw binary components
     * - Base64-encodes each component for database storage
     * - Used in register.php to store encrypted phone number
     * 
     * @param string $plaintext Data to encrypt (e.g., phone number)
     * @return array Associative array with base64-encoded components:
     *         ['encrypted' => base64_string, 'iv' => base64_string, 'tag' => base64_string]
     * 
     * DATABASE COLUMNS USED:
     * - phone_encrypted: TEXT column storing base64(ciphertext)
     * - phone_iv: TEXT column storing base64(IV)
     * - phone_tag: TEXT column storing base64(tag)
     */
    public static function encryptForStorage($plaintext) {
        // Use self:: keyword to call static method encrypt() from same class
        // Get raw binary encrypted components
        $result = self::encrypt($plaintext);
        
        // Use base64_encode() to convert binary data to text-safe base64 string
        // Each component encoded separately for database storage
        // Base64 ensures binary data is safely stored as TEXT column
        return [
            'encrypted' => base64_encode($result['encrypted']),  // Encode ciphertext
            'iv' => base64_encode($result['iv']),                // Encode IV
            'tag' => base64_encode($result['tag'])               // Encode tag
        ];
    }

    /**
     * DECRYPTFROMSTORAGE METHOD - Decryption from database format
     * 
     * PURPOSE: Decrypts data retrieved from database
     * - Base64-decodes components from database
     * - Calls decrypt() with binary data
     * - Used in login.php to decrypt stored phone number
     * 
     * @param string $base64_encrypted Base64-encoded ciphertext from database
     * @param string $base64_iv Base64-encoded IV from database
     * @param string $base64_tag Base64-encoded tag from database
     * @return string Decrypted plaintext
     * @throws Exception if decoding or decryption fails
     * 
     * PROCESS:
     * 1. Retrieve three components from database (all base64-encoded)
     * 2. Decode each component from base64 to binary
     * 3. Pass binary components to decrypt() method
     * 4. Return plaintext
     */
    public static function decryptFromStorage($base64_encrypted, $base64_iv, $base64_tag) {
        try {
            // Use base64_decode($string, true) to convert base64 string to binary
            // Second parameter true enables strict validation
            $encrypted = base64_decode($base64_encrypted, true);
            
            // Use base64_decode() to convert IV from base64 to binary
            $iv = base64_decode($base64_iv, true);
            
            // Use base64_decode() to convert tag from base64 to binary
            $tag = base64_decode($base64_tag, true);
            
            // Use === false comparison to check if any base64_decode() failed
            // base64_decode() returns false on invalid input
            if ($encrypted === false || $iv === false || $tag === false) {
                // Use throw new Exception to indicate decoding error
                throw new Exception('Invalid base64 encoding in encrypted data');
            }
            
            // Use self:: to call static decrypt() method with decoded binary components
            // decrypt() will perform decryption and verify authentication tag
            return self::decrypt($encrypted, $iv, $tag);
        } catch (Exception $e) {
            // Use catch block to handle any exceptions thrown above
            // Use throw new Exception to re-throw with additional context
            throw new Exception('Decryption from storage failed: ' . $e->getMessage());
        }
    }
}
?>

