<?php
declare(strict_types=1);

$uploadsRoot = $argv[1] ?? '';
if ($uploadsRoot === '' || !is_dir($uploadsRoot)) {
    fwrite(STDERR, "Usage: php generate-homepage-derivatives.php /absolute/path/to/wp-content/uploads\n");
    exit(1);
}

$jobs = [
    [
        'source' => '2026/02/gastronom-logo.png',
        'target' => '2026/02/gastronom-logo-home-200.webp',
        'mode' => 'contain',
        'width' => 200,
        'height' => 200,
        'quality' => 86,
    ],
    [
        'source' => '2025/09/image_2400x1200.png',
        'target' => '2025/09/image_2400x1200-home-1600.webp',
        'mode' => 'contain',
        'width' => 1600,
        'height' => 800,
        'quality' => 84,
    ],
    [
        'source' => '2026/02/Alko.jpg.webp',
        'target' => '2026/02/Alko-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/close-up-futuristic-soft-drink-scaled.jpg.webp',
        'target' => '2026/02/close-up-futuristic-soft-drink-scaled-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/Dzhemy.jpg.webp',
        'target' => '2026/02/Dzhemy-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/68dd38210edc84693653f675-1.jpeg.webp',
        'target' => '2026/02/68dd38210edc84693653f675-1-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/Karamel.jpg.webp',
        'target' => '2026/02/Karamel-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/68de59a50edc846936543a75-1-scaled.jpeg.webp',
        'target' => '2026/02/68de59a50edc846936543a75-1-scaled-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/Konfety.jpg.webp',
        'target' => '2026/02/Konfety-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/68de579d0edc846936543a05-1-scaled.jpeg.webp',
        'target' => '2026/02/68de579d0edc846936543a05-1-scaled-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/Lapsha.jpg.webp',
        'target' => '2026/02/Lapsha-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/68dd3840de9da6af4df9450f-1.jpeg.webp',
        'target' => '2026/02/68dd3840de9da6af4df9450f-1-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/68de5c100edc846936543b2c-1.jpeg.webp',
        'target' => '2026/02/68de5c100edc846936543b2c-1-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/68dd3854de9da6af4df94511-1.jpeg.webp',
        'target' => '2026/02/68dd3854de9da6af4df94511-1-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/Konservatsiya.jpg.webp',
        'target' => '2026/02/Konservatsiya-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/68de5c40de9da6af4df98a4c-1.jpeg.webp',
        'target' => '2026/02/68de5c40de9da6af4df98a4c-1-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/68de5c5ade9da6af4df98a4f-1.jpeg.webp',
        'target' => '2026/02/68de5c5ade9da6af4df98a4f-1-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/68dd3880d82091e41c7c9b59-1.jpeg.webp',
        'target' => '2026/02/68dd3880d82091e41c7c9b59-1-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/68de5c770edc846936543b3f-1.jpeg.webp',
        'target' => '2026/02/68de5c770edc846936543b3f-1-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/Dllya-zdorovya.jpg.webp',
        'target' => '2026/02/Dllya-zdorovya-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/Prazdnichnye.jpg.webp',
        'target' => '2026/02/Prazdnichnye-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/Hleb.jpg.webp',
        'target' => '2026/02/Hleb-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/02/ad3dd88d6df011eeb16356181a0358a2_upscaled.jpeg.webp',
        'target' => '2026/02/ad3dd88d6df011eeb16356181a0358a2_upscaled-home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
    [
        'source' => '2026/03/Pngtree-dark-chocolate-bar-on-white_15459579-1-.png',
        'target' => '2026/03/Pngtree-dark-chocolate-bar-on-white_15459579-1--home-560.webp',
        'mode' => 'cover',
        'width' => 560,
        'height' => 560,
        'quality' => 82,
    ],
];

foreach ($jobs as $job) {
    $sourcePath = $uploadsRoot . '/' . $job['source'];
    $targetPath = $uploadsRoot . '/' . $job['target'];

    if (!is_file($sourcePath)) {
        fwrite(STDERR, "MISSING {$job['source']}\n");
        continue;
    }

    $image = imageFromPath($sourcePath);
    if (!$image) {
        fwrite(STDERR, "FAILED_LOAD {$job['source']}\n");
        continue;
    }

    $result = resizeImage(
        $image,
        imagesx($image),
        imagesy($image),
        (int) $job['width'],
        (int) $job['height'],
        $job['mode']
    );

    if (!is_dir(dirname($targetPath))) {
        mkdir(dirname($targetPath), 0775, true);
    }

    imagepalettetotruecolor($result);
    imagealphablending($result, true);
    imagesavealpha($result, true);
    imagewebp($result, $targetPath, (int) $job['quality']);

    imagedestroy($image);
    imagedestroy($result);

    $sourceSize = filesize($sourcePath);
    $targetSize = filesize($targetPath);
    echo implode("\t", [
        $job['source'],
        $job['target'],
        (string) $sourceSize,
        (string) $targetSize,
    ]) . PHP_EOL;
}

function imageFromPath(string $path)
{
    $mime = mime_content_type($path) ?: '';

    return match ($mime) {
        'image/jpeg' => imagecreatefromjpeg($path),
        'image/png' => imagecreatefrompng($path),
        'image/webp' => imagecreatefromwebp($path),
        default => null,
    };
}

function resizeImage($source, int $sourceWidth, int $sourceHeight, int $targetWidth, int $targetHeight, string $mode)
{
    $destination = imagecreatetruecolor($targetWidth, $targetHeight);
    imagealphablending($destination, false);
    imagesavealpha($destination, true);
    $transparent = imagecolorallocatealpha($destination, 0, 0, 0, 127);
    imagefilledrectangle($destination, 0, 0, $targetWidth, $targetHeight, $transparent);

    if ($mode === 'cover') {
        $scale = max($targetWidth / $sourceWidth, $targetHeight / $sourceHeight);
        $scaledWidth = (int) ceil($sourceWidth * $scale);
        $scaledHeight = (int) ceil($sourceHeight * $scale);
        $destinationX = (int) floor(($targetWidth - $scaledWidth) / 2);
        $destinationY = (int) floor(($targetHeight - $scaledHeight) / 2);

        imagecopyresampled(
            $destination,
            $source,
            $destinationX,
            $destinationY,
            0,
            0,
            $scaledWidth,
            $scaledHeight,
            $sourceWidth,
            $sourceHeight
        );

        return $destination;
    }

    $scale = min($targetWidth / $sourceWidth, $targetHeight / $sourceHeight, 1);
    $scaledWidth = (int) round($sourceWidth * $scale);
    $scaledHeight = (int) round($sourceHeight * $scale);
    $destinationX = (int) floor(($targetWidth - $scaledWidth) / 2);
    $destinationY = (int) floor(($targetHeight - $scaledHeight) / 2);

    imagecopyresampled(
        $destination,
        $source,
        $destinationX,
        $destinationY,
        0,
        0,
        $scaledWidth,
        $scaledHeight,
        $sourceWidth,
        $sourceHeight
    );

    return $destination;
}
