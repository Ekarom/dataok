<?php
// Simple image gallery for images directory
$dir = __DIR__;
$images = glob($dir . "/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Images Gallery</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: #f5f5f5;
        }
        h1 {
            color: #333;
        }
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .image-card {
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .image-card img {
            max-width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 4px;
        }
        .image-name {
            margin-top: 10px;
            font-size: 12px;
            color: #666;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <h1>Images Directory</h1>
    <p>Total images: <?php echo count($images); ?></p>
    
    <div class="gallery">
        <?php foreach ($images as $image): ?>
            <?php $filename = basename($image); ?>
            <div class="image-card">
                <a href="<?php echo $filename; ?>" target="_blank">
                    <img src="<?php echo $filename; ?>" alt="<?php echo $filename; ?>">
                </a>
                <div class="image-name"><?php echo $filename; ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
