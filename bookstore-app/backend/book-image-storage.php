<?php

/**
 * Shared helpers for storing cropped book covers plus their original image source
 * and crop positioning metadata without changing the current database schema.
 */

function ensureBookUploadsDirectory(): string
{
    $uploadsDir = __DIR__ . '/uploads/books';

    if (!is_dir($uploadsDir) && !mkdir($uploadsDir, 0777, true)) {
        throw new Exception("Failed to create uploads directory: $uploadsDir");
    }

    if (!is_writable($uploadsDir)) {
        throw new Exception("Uploads directory is not writable: $uploadsDir");
    }

    return $uploadsDir;
}

function extractBookImageFilename(?string $imageReference): ?string
{
    if (!$imageReference || !is_string($imageReference)) {
        return null;
    }

    $trimmedReference = trim($imageReference);
    if ($trimmedReference === '' || strpos($trimmedReference, 'data:image') === 0) {
        return null;
    }

    $path = parse_url($trimmedReference, PHP_URL_PATH);
    $filename = basename($path ?: $trimmedReference);

    return $filename !== '' ? $filename : null;
}

function bookImageFileExists(?string $imageReference): bool
{
    $filename = extractBookImageFilename($imageReference);
    if (!$filename) {
        return false;
    }

    $imagePath = ensureBookUploadsDirectory() . '/' . $filename;
    return file_exists($imagePath);
}

function saveBase64BookImage(string $base64Image, string $prefix = 'book'): string
{
    if (strpos($base64Image, 'data:image') !== 0) {
        throw new Exception('Invalid image payload');
    }

    preg_match('/data:image\/(\w+);base64,/', $base64Image, $matches);
    $imageType = $matches[1] ?? 'jpg';

    $base64Data = substr($base64Image, strpos($base64Image, ',') + 1);
    $imageData = base64_decode($base64Data, true);

    if ($imageData === false) {
        throw new Exception('Failed to decode base64 image data');
    }

    $uploadsDir = ensureBookUploadsDirectory();
    $filename = $prefix . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $imageType;
    $imagePath = $uploadsDir . '/' . $filename;

    if (file_put_contents($imagePath, $imageData, LOCK_EX) === false) {
        throw new Exception("Failed to save book image to: $imagePath");
    }

    return $filename;
}

function deleteBookImageFile(?string $imageReference): void
{
    $filename = extractBookImageFilename($imageReference);
    if (!$filename) {
        return;
    }

    $imagePath = ensureBookUploadsDirectory() . '/' . $filename;
    if (file_exists($imagePath) && !unlink($imagePath)) {
        error_log('Warning: Failed to delete image file: ' . $filename);
    }
}

function getBookImageMetadataPath(): string
{
    return ensureBookUploadsDirectory() . '/book-image-metadata.json';
}

function readBookImageMetadata(): array
{
    $metadataPath = getBookImageMetadataPath();
    if (!file_exists($metadataPath)) {
        return [];
    }

    $rawMetadata = file_get_contents($metadataPath);
    if ($rawMetadata === false || trim($rawMetadata) === '') {
        return [];
    }

    $decodedMetadata = json_decode($rawMetadata, true);
    return is_array($decodedMetadata) ? $decodedMetadata : [];
}

function writeBookImageMetadata(array $metadata): void
{
    $metadataPath = getBookImageMetadataPath();
    $encodedMetadata = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

    if ($encodedMetadata === false) {
        throw new Exception('Failed to encode book image metadata');
    }

    if (file_put_contents($metadataPath, $encodedMetadata, LOCK_EX) === false) {
        throw new Exception("Failed to save book image metadata to: $metadataPath");
    }
}

function getBookImageState(int $bookId): array
{
    $metadata = readBookImageMetadata();
    $entry = $metadata[(string) $bookId] ?? [];
    $originalImageFilename = isset($entry['book_cover_original_image']) && $entry['book_cover_original_image'] !== ''
        ? $entry['book_cover_original_image']
        : null;

    if ($originalImageFilename && !bookImageFileExists($originalImageFilename)) {
        $originalImageFilename = null;
    }

    return [
        'book_cover_original_image' => $originalImageFilename,
        'image_scale' => isset($entry['image_scale']) ? floatval($entry['image_scale']) : 1.0,
        'image_offset_x' => isset($entry['image_offset_x']) ? floatval($entry['image_offset_x']) : 0.0,
        'image_offset_y' => isset($entry['image_offset_y']) ? floatval($entry['image_offset_y']) : 0.0,
    ];
}

function setBookImageState(
    int $bookId,
    ?string $originalImageFilename,
    float $scale,
    float $offsetX,
    float $offsetY
): void {
    $metadata = readBookImageMetadata();
    $metadata[(string) $bookId] = [
        'book_cover_original_image' => $originalImageFilename,
        'image_scale' => $scale,
        'image_offset_x' => $offsetX,
        'image_offset_y' => $offsetY,
    ];

    writeBookImageMetadata($metadata);
}

function deleteBookImageState(int $bookId): void
{
    $metadata = readBookImageMetadata();
    $bookKey = (string) $bookId;

    if (!array_key_exists($bookKey, $metadata)) {
        return;
    }

    unset($metadata[$bookKey]);
    writeBookImageMetadata($metadata);
}
