<?php
try {
    $image = new \Imagick();
    $image->newImage(100, 100, 'white');
    $image->setImageFormat('png');
    $image->writeImage('test_output.png');
    echo "ImageMagick is working correctly!\n";
} catch (Exception $e) {
    echo "Error testing ImageMagick: " . $e->getMessage() . "\n";
    echo "Please check if ImageMagick is properly installed and accessible from PHP.\n";
}
