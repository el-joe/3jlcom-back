<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\LaravelImageOptimizer\Facades\ImageOptimizer;

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

        foreach ($files as $file) {
            // check if file is image by file name extension
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'])) {
                continue;
            }
            // get file size in kb
            $this->info('Size Before: ' . (filesize($file) / 1024).'KB');
            $this->optimizeImage($file);
            $this->info('Size After: ' . (filesize($file) / 1024).'KB');
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
        $this->info("Image optimized: " . $path);
    }
}
