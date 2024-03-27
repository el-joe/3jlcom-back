<?php

namespace App\Console\Commands;

use App\Models\City;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class CitiesImageFromGoogle extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:cities-image-from-google';

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

        City::whereNull('image')->chunk(100, function ($cities) {
            foreach ($cities as $city) {
                $city->image = $this->getCityImage($city->city);
                $city->save();
            }
        });

    }

    function getCityImage($cityName) {
        $client = new Client();

        $_imgresponse = $client->request('GET', 'https://www.google.com/search?tbm=isch&q=HighQualityImageFor' . urlencode($cityName));
        $html = $_imgresponse->getBody()->getContents();

        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/', $html, $matches);

        $imageUrls = $matches[1] ?? [];

        if (count($imageUrls) > 1) {
            return $imageUrls[1];
        }

        return null;
    }
}
