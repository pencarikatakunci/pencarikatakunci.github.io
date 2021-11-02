<?php
// your access key, can be found here https://www.site-shot.com/dashboard/
define('ACCESS_KEY', 'IAAIEYKBJANJHZ6IMZ3RRH6YN6');
// api endpoint
define('API_URI', 'https://api.site-shot.com'); 
// folder for the cached screenshots
define('CACHE_DIR', '/tmp');
// how much time screenshot will be cached, defined in seconds
define('CACHE_EXPIRATION', 60*60*24*1); 
// Screenshot parameters
// screenshot witdh
define('SCR_WIDTH', 1024);
// screenshot heigth, or minimum height in case if full size option enabled
define('SCR_HEIGTH', 1920);
// delay time in miliseconds, to make sure all the content is loadded
define('SCR_DELAY_TIME', 3000);
// timeout in miliseconds 0-120000, maximum time allocated for the screenshot creation
// in case if timeout too small timeout error would be recieved
define('SCR_TIMEOUT', 90000);

function downloadScreenshot($url)
{
    $query_string = http_build_query(array(
        'url' => $url,
        'userkey' => ACCESS_KEY,
        'width' => SCR_WIDTH,
        'height' => SCR_HEIGTH,
        'delay_time' => SCR_DELAY_TIME,
        'timeout' => SCR_TIMEOUT,
        'response_type' => 'json',
    ));
    $image = file_get_contents(API_URI."/?$query_string");
    return $image;
}

function getScreenshot($url)
{
    $file_name = CACHE_DIR . '/' . md5($url);
    $screenshot_json = False;
    if (CACHE_EXPIRATION > 0 and file_exists($file_name)) {
        if (filemtime($file_name) + CACHE_EXPIRATION > time()) {
            $screenshot_json = file_get_contents($file_name);
        }
    }

    if (! $screenshot_json) {
        $screenshot_json = downloadScreenshot($url);
        if (CACHE_EXPIRATION > 0) {
            file_put_contents($file_name, $screenshot_json);
        }
    }
    return $screenshot_json;
}

function run()
{
    $url = 'https://youtube.com';
    $screenshot_json = json_decode(getScreenshot($url));
    $data = explode(',', $screenshot_json->image);
    $image = base64_decode($data[1]);
    header("Content-Type: image/png");
    echo($image);
}
run();

?>
