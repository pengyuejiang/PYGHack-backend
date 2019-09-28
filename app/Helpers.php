<?php

namespace App;

use Illuminate\Support\Arr;
use App\Helpers\ErrorHelpers;

class Helpers
{
    // transform
    public static function toObject($input): object
    {
        return json_decode(json_encode($input));
    }

    public static function toArray($input): array
    {
        return json_decode(is_object($input) || is_array($input) ? json_encode($input) : $input, true);
    }

    // parse
    public static function parseBoolean($input): bool
    {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN);
    }

    public static function parseNull($input)
    {
        if ($input === 'null') {
            return null;
        } else {
            return $input;
        }
    }

    public static function parseHTML($input, bool $isBreak = false)
    {
        // decode tags
        $out = html_entity_decode(htmlspecialchars_decode($input));

        // decode <a>
        $out = preg_replace(
            "#\<a.+href\=[\"|\'](.+)[\"|\'].*title\=[\"|\'](.+)[\"|\'].*\>.*\<\/a\>#U",
            "![$2]($1)",
            $out
        );

        $out = preg_replace("#\<a.+href\=[\"|\'](.+)[\"|\'].*\>.*\<\/a\>#U", "![]($1)", $out);

        // decode <img>
        $out = preg_replace("#\<img.+src\=[\"|\'](.+)[\"|\'].*\>#U", "![]($1)", $out);

        // decode line-breakers
        if ($isBreak) {
            $out = preg_replace("#\<\/p\>#U", "\n\n", $out);
            $out = preg_replace("#\<br\>#U", "\n\n", $out);
            $out = preg_replace("#\<\\br\>#U", "\n\n", $out);
        }

        // strip
        return trim(strip_tags($out));
    }

    // array opt
    public static function indexArrayByKey($array, $index): array
    {
        $out = [];
        foreach ($array as $a) {
            $out[$a[$index]] = $a;
        }
        return $out;
    }

    public static function countArrayMaxDepth($array): int
    {
        $max = 0;

        foreach ($array as $v) {
            if (is_array($v)) {
                $depth = self::countArrayMaxDepth($v) + 1;

                if ($depth > $max) {
                    $max = $depth;
                }
            }
        }

        return $max;
    }

    public static function arrayGetValByKeyOrFail(
        array $array,
        string $key,
        int $errorCode = 2001,
        string $errorMsg = ''
    ) {
        if (isset($array[$key])) {
            return $array[$key];
        } else {
            app(ErrorHelpers::class)->throw($errorCode, $errorMsg);
        }
    }

    public static function groupArrayByKey($array, $index): array
    {
        $out = [];
        foreach ($array as $a) {
            if (!isset($out[$a[$index]])) {
                $out[$a[$index]] = [];
            }
            $out[$a[$index]][] = $a;
        }
        return $out;
    }

    public static function getArrayElementsByKey($array, $index): array
    {
        $out = [];
        foreach ($array as $a) {
            $out[] = $a[$index];
        }
        return $out;
    }

    public static function randPickFromArray($array)
    {
        $randKey = array_rand($array);
        return $array[$randKey];
    }

    public static function only(array $array, array $keys)
    {
        return Arr::only($array, $keys);
    }

    // sort
    public static function sortCreateAtDesc(array &$array)
    {
        usort($array, function ($a, $b) {
            return date_create($a['created_at']) < date_create($b['created_at']);
        });
    }

    public static function sortCreateAtAsc(array &$array)
    {
        usort($array, function ($a, $b) {
            return date_create($a['created_at']) > date_create($b['created_at']);
        });
    }

    // validate
    public static function isJson($string)
    {
        if (!is_string($string) || is_numeric($string)) {
            return false;
        }
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function arrayHasKey(array $array, array $keys)
    {
        foreach ($keys as $key) {
            if (isset($array[$key])) {
                return true;
            }
        }
        return false;
    }

    public static function isValidateDateFormat($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        // The Y ( 4 digits year ) returns TRUE for any integer with
        // any number of digits so changing the comparison from == to === fixes the issue.
        return $d && $d->format($format) === $date;
    }

    // cache
    public static function cacheManager(string $module, string $method, string $key, int $minutes, callable $f)
    {
        $key = $module.$method.$key;
        if (!\Cache::store('redis')->has($key)) {
            $out = $f();
            \Cache::store('redis')->put(
                $key,
                is_array($out) ? json_encode($out) : $out,
                $minutes
            );
        } else {
            $out = \Cache::store('redis')->get($key);
            if (self::isJson($out)) {
                $out = json_decode($out, true);
            }
        }
        return $out;
    }

    public static function hasCache(string $module, string $method, string $key)
    {
        $key = $module.$method.$key;
        return \Cache::store('redis')->has($key);
    }

    public static function getCache(string $module, string $method, string $key)
    {
        $key = $module.$method.$key;
        $out = \Cache::store('redis')->get($key);
        if (self::isJson($out)) {
            $out = json_decode($out, true);
        }
        return $out;
    }

    public static function putCache(string $module, string $method, string $key, int $minutes, $content)
    {
        $key = $module.$method.$key;

        if (!is_string($content)) {
            $content = json_encode($content);
        }

        return \Cache::store('redis')->put($key, $content, $minutes);
    }

    public static function deleteCache(string $module, string $method, string $key)
    {
        $key = $module.$method.$key;
        \Cache::store('redis')->delete($key);
    }
    
    // cryptography
    public static function encrypt(string $input, bool $symmetric = false): string
    {
        if (!$symmetric) {
            return md5($input);
        } else {
            $iv = 112324234231;
            return \openssl_encrypt($input, 'AES-256-CBC', \env('APP_KEY'), 0, $iv);
        }
    }

    public static function decrypt(string $input)
    {
        return \openssl_decrypt($input, 'AES-256-CBC', \env('APP_KEY'));
    }

    public static function randomKey($length = 35)
    {
        return \str_random($length);
    }

    // env operation
    public static function setEnvValue(array $values)
    {
        $envFile = \config_path().'/../.env';
        $str = file_get_contents($envFile);

        if (count($values) > 0) {
            foreach ($values as $envKey => $envValue) {
                $str .= "\n"; // In case the searched variable is in the last line without \n
                $keyPosition = strpos($str, "{$envKey}=");
                $endOfLinePosition = strpos($str, "\n", $keyPosition);
                $oldLine = substr($str, $keyPosition, $endOfLinePosition - $keyPosition);

                // If key does not exist, add it
                if (!$keyPosition || !$endOfLinePosition || !$oldLine) {
                    $str .= "{$envKey}={$envValue}\n";
                } else {
                    $str = str_replace($oldLine, "{$envKey}={$envValue}", $str);
                }
            }
        }

        $str = substr($str, 0, -1);
        if (!file_put_contents($envFile, $str)) {
            app(ErrorHelpers::class)->throw(2001, 'could not write to .env file');
        }
    }

    // sys operation
    public static function chmod($path, $filePerm = 0644, $dirPerm = 0755)
    {
        // Check if the path exists
        if (!file_exists($path)) {
            return false;
        }
 
        // See whether this is a file
        if (is_file($path)) {
            // Chmod the file with our given filepermissions
            chmod($path, $filePerm);
 
        // If this is a directory...
        } elseif (is_dir($path)) {
            // Then get an array of the contents
            $foldersAndFiles = scandir($path);
 
            // Remove "." and ".." from the list
            $entries = array_slice($foldersAndFiles, 2);
 
            // Parse every result...
            foreach ($entries as $entry) {
                // And call this function again recursively, with the same permissions
                self::chmod($path."/".$entry, $filePerm, $dirPerm);
            }
 
            // When we are done with the contents of the directory, we chmod the directory itself
            chmod($path, $dirPerm);
        }
 
        // Everything seemed to work out well, return true
        return true;
    }

    public static function generateKey($length = 6)
    {
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $code .= (string)mt_rand(0, 9);
        }

        return $code;
    }
}
