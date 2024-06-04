<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;
use Spatie\Image\Image;

class OptimizeImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $files = $this->getPathFilesUsingRecursive(public_path('images'));

        foreach ($files as $key=>$file) {
            // skip file when last modified is less than 1 day
            if (filemtime($file) > strtotime('-2 day')) {
                continue;
            }
            $getPercentageFromCountAndKey = (($key+1) / count($files)) * 100;
            // check if file is image by file name extension
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                continue;
            }
            $this->info("Image optimized: " . $file);
            $before = round(filesize($file) / 1024).'KB';
            $this->resizeImage($file);
            $this->optimizeImage($file);
            $after = round(filesize($file) / 1024).'KB';
            $this->info("Optimize -> Before: " . $before . " - After: " . $after);
            $this->info("Progress: " . round($getPercentageFromCountAndKey, 2) . "%" . " - " . $key . "/" . count($files));
        }
    }

    function getPathFilesUsingRecursive($dir, &$results = array()) {
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                $this->getPathFilesUsingRecursive($path, $results);
                $results[] = $path;
            }
        }
        return $results;
    }

    function optimizeImage($path) {
        ImageOptimizer::optimize($path);
    }

    function resizeImage($path) {
        // check if image is jpeg or png or jpg or gif
        if(!in_array(pathinfo($path, PATHINFO_EXTENSION), ['jpg', 'jpeg', 'png', 'gif'])) {
            return;
        }
        try{
            if(!getimagesize($path)) {
                return;
            }
            $width = Image::load($path)->getWidth();
            $height = Image::load($path)->getHeight();
            if($height >= 2000 || $width >= 2000) {
                $newWidth = $width * (30/100);
                $newHeight = $height * (30/100);
                Image::load($path)->width($newWidth)->height($newHeight)->save($path);
            }elseif($height >= 1500 || $width >= 1500) {
                    $newWidth = $width * (40/100);
                    $newHeight = $height * (40/100);
                    Image::load($path)->width($newWidth)->height($newHeight)->save($path);
            }elseif($height >= 800 || $width >= 800) {
                $newWidth = $width * (70/100);
                $newHeight = $height * (70/100);
                Image::load($path)->width($newWidth)->height($newHeight)->save($path);
            }else{
                $newWidth = $width;
                $newHeight = $height;
            }
            $this->info("Height -> $height:$newHeight | Width -> $width:$newWidth");
        }
        catch(\Exception $e) {
            info('---------------------------- Error: ' . $path);
            info($e->getMessage());
        }
    }
}
