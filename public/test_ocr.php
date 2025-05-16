<?php
require __DIR__ . '/../vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

try {
    // Test with a simple image
    $testImage = __DIR__ . '/test.png';
    
    // First try with optimized settings
    $ocr = new TesseractOCR($testImage);
    $ocr->lang('eng')
        ->psm(3)
        ->oem(1)
        ->whitelist('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
        ->config('tessedit_char_whitelist', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789')
        ->config('user_defined_dpi', 300);

    $result = $ocr->run();
    echo "Test with optimized settings: " . $result . "\n";
    
    // Try with basic settings
    $basicResult = (new TesseractOCR($testImage))
        ->lang('eng')
        ->run();
    
    echo "Test with basic settings: " . $basicResult . "\n";
    
    echo "Tesseract OCR is working correctly!\n";
} catch (Exception $e) {
    echo "Error testing Tesseract: " . $e->getMessage() . "\n";
    echo "Please check if Tesseract is properly installed and accessible from PHP.\n";
}
