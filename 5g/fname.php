<?php

class test
{
    const FOLDER_IMAGES = 'images/logo_svg-IMagick/';
    public string $logo_url;
    public string $file;
    public static array $logoSizes = [
        180,
        152,
        144,
        120,
        114,
        76,
        72,
        60,
        57,
    ];

    public function getFaviconNames(): array
    {
        $path = pathinfo($this->logo_url, PATHINFO_DIRNAME);
        $file = pathinfo($this->logo_url, PATHINFO_FILENAME);
        $ext = pathinfo($this->logo_url, PATHINFO_EXTENSION);
        $sizes = self::$logoSizes;
        $out = [];
        foreach ($sizes as $size) {
            $fileName = $path . '/' . $file . '_' . $size . 'x' . $size . '.' . $ext;
            $out[] = $fileName;
        }
        return $out;
    }

    protected function generateFileName($file, $size): string
    {
        $nameEscaped = preg_replace('/\s+/', '_', $file);
        if ($size) {
            $fileStr = pathinfo($this->logo_url, PATHINFO_FILENAME);
            $fileArr = explode('_', $fileStr);
            $time = end($fileArr);
        } else {
            $time = time();
        }
        return 'logo_' . $nameEscaped . '_' . $time . ($size ? '_' . $size . 'x' . $size : '') . '.' . pathinfo($file, PATHINFO_EXTENSION);
//        return 'logo_' . $nameEscaped . '_' . time() . ($size ? '_' . $size . 'x' . $size : '') . '.' . pathinfo($file, PATHINFO_EXTENSION);
    }

    protected function resizeSvg(&$svg, $size)
    {
        $svg = preg_replace('/width="\d+"/', 'width="' . $size . '"', $svg);
        $svg = preg_replace('/height="\d+"/', 'height="' . $size . '"', $svg);
    }

    /**
     * @throws ImagickException
     */
    protected function loadFileInstance($appleTouchIconName, $file, $size): void
    {
        $dir = pathinfo($appleTouchIconName, PATHINFO_DIRNAME);
        if (!realpath($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!$size) {
            copy($file, $appleTouchIconName);
        } else {
            $dimensions = getimagesize($file);
            if ($dimensions && $dimensions['mime'] == 'image/png') {
                $src = imagecreatefrompng($file);
                $dimensions = getimagesize($file);
                $width = $dimensions[0];
                $height = $dimensions[1];
                $tmp = imagecreatetruecolor($size, $size);
                imagealphablending($tmp, false);
                imagesavealpha($tmp, true);
                $transparent = imagecolorallocatealpha($tmp, 255, 255, 255, 127);
                imagefilledrectangle($tmp, 0, 0, $size, $size, $transparent);
                imagecopyresampled($tmp, $src, 0, 0, 0, 0, $size, $size, $width, $height);
                imagepng($tmp, $appleTouchIconName);
            } elseif (($dimensions && $dimensions['mime'] == 'image/svg+xml') || (strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'svg')) {
                $appleTouchIconName = pathinfo($appleTouchIconName, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR . pathinfo($appleTouchIconName, PATHINFO_FILENAME) . '.png';
                $svg = file_get_contents($file);
                    $this->resizeSvg($svg, $size);
//                    echo $svg."\n\n";
                    $image = new IMagick();
                    $image->setBackgroundColor(new ImagickPixel('transparent'));
                    $image->readImageBlob($svg);
//            $image->setImageResolution($size, $size);
//            $image->extentImage($size, $size, 0, 0);
//            $image->resizeImage($size, $size, 0, 1, true);
//            $image->scaleImage($size, $size, true);
                    $image->setImageFormat("png32");
                    $image->writeImage($appleTouchIconName . '.png');
                    $image->destroy();
/*
                $command = 'rsvg-convert -w ' . $size . ' -h ' . $size . ' logo.svg > ' . $appleTouchIconName;
                exec($command);
*/
/*
                $svgStr = file_get_contents($file);
                $svg = new SvgToImage($svgStr);
                $svg->setWidth($size);
                $svg->setHeight($size);
                $svg->toImage('png', $appleTouchIconName . '.png');
*/
            }
        }
    }

    public function run(): void
    {
        $file = $this->file;
        $sizes = array_merge([false], self::$logoSizes);
        foreach ($sizes as $size) {
            $fileName = $this->generateFileName($file, $size);
            $appleTouchIconName = self::FOLDER_IMAGES . $fileName;
            $this->loadFileInstance($appleTouchIconName, $file, $size);
        }

    }
}

//include 'SvgToImage.php';
//include 'SvgTest.php';
$f = new test();
//$f->file = 'logo.png';
$f->file = 'logo.svg';
$f->logo_url = 'images/logo/logo.svg';
$f->run();
//$pres = new Presentation();

//print_r($f->getFaviconNames());
//var_dump(realpath(test::FOLDER_IMAGES));
//print_r(getimagesize($f->file));