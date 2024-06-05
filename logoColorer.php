<?php

// Load the logo
$LogoPath = 'JcaaLogo.png';
$logo = imagecreatefrompng($LogoPath);

if (!$logo) {
    error_log("Error: Unable to load the image.");
    exit;
}

// Get the dimensions of the image
$imageWidth = imagesx($logo);
$imageHeight = imagesy($logo);

// Generate a random RGB color (not too bright, not too dark!)
function generateRandomColor() {
    $values = [30, 200, mt_rand(30, 200)];
    shuffle($values); // Randomize the order of the values
    return [
        'r' => $values[0],
        'g' => $values[1],
        'b' => $values[2],
    ];
}

$rgb = generateRandomColor();
$red = $rgb['r'];
$green = $rgb['g'];
$blue = $rgb['b'];

// Create a new true color image
$coloredImage = imagecreatetruecolor($imageWidth, $imageHeight);

// Apply the random color to the logo
for ($x = 0; $x < $imageWidth; $x++) {
    for ($y = 0; $y < $imageHeight; $y++) {
        // Get the color index of the pixel
        $colorIndex = imagecolorat($logo, $x, $y);
        $colorComponents = imagecolorsforindex($logo, $colorIndex);

        // Calculate the new color based on the grayscale value and random color
        $gray = $colorComponents['red'];  // Since it's a black and white image, red, green, and blue values are the same
        if ($gray < 191) {
            // Dark pixels: set to desired RGB
            $newRed = $red;
            $newGreen = $green;
            $newBlue = $blue;
        } else {
            // Bright pixels: leave unchanged
            $newRed = $colorComponents['red'];
            $newGreen = $colorComponents['green'];
            $newBlue = $colorComponents['blue'];
        }

        // Allocate the new color in the colored image
        $newColor = imagecolorallocate($coloredImage, $newRed, $newGreen, $newBlue);

        // Set the pixel in the new image
        imagesetpixel($coloredImage, $x, $y, $newColor);
    }
}

// Output the image with proper headers
header('Content-Type: image/png');
imagepng($coloredImage);

// Free up memory
imagedestroy($logo);
imagedestroy($coloredImage);
?>
