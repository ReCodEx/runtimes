<?php

/*
 * Lib file with shared functions.
 */

/**
 * Load manifest from a zip package and parse it.
 */
function loadManifest(string $fileName): array
{
    $zip = new ZipArchive();
    // TODO: ZipArchive::RDONLY flag would be nice here, but it requires PHP 7.4.3+
    $opened = $zip->open($fileName);
    if ($opened !== true) {
        throw new RuntimeException("Unable to open file '$fileName' for reading (code $opened).");
    }

    $str = $zip->getFromName('manifest.json');
    if (!$str) {
        throw new RuntimeException("No 'manifest.json' file is present in the ZIP package.");
    }

    $json = json_decode($str, true, 512, JSON_THROW_ON_ERROR);
    if (!$json) {
        throw new RuntimeException("The manifest is not a valid JSON file.");
    }

    return $json;
}

/**
 * Compare associative arrays deeply, return true if they are the same.
 */
function deepCompare($arr1, $arr2): bool
{
    if (is_array($arr1) !== is_array($arr2)) {
        return false;
    }

    if (!is_array($arr1)) {
        return $arr1 === $arr2; // compare scalars
    }

    if (count($arr1) !== count($arr2)) {
        return false;
    }

    $keys = array_keys($arr1);
    sort($keys);

    foreach ($keys as $key) {
        if (!array_key_exists($key, $arr2) || !deepCompare($arr1[$key], $arr2[$key])) {
            return false;
        }
    }

    return true;
}

/**
 * Go through list of args, validate the files/dirs exists, use glob to search directories.
 */
function preprocessFileArgs($args): array
{
    $res = [];
    foreach ($args as $arg) {
        if (is_dir($arg)) {
            foreach (glob("$arg/*.zip") as $f) {
                $res[$f] = true;
            }
        } elseif (is_file($arg)) {
            $res[$arg] = true;
        } else {
            throw new RuntimeException("Argument $arg does not refer to existing file nor directory.");
        }
    }

    return array_keys($res);
}

/**
 * Combine multiple name sets into one and sort it.
 */
function mergeNameSets($sets, $ignore): array
{
    if (!is_array($ignore)) {
        $ignore = [ $ignore ];
    }

    $res = [];
    foreach ($sets as $set) {
        foreach ($set as $name) {
            $res[$name] = true;
        }
    }

    foreach ($ignore as $i) {
        unset($res[$i]);
    }

    $res = array_keys($res);
    sort($res);
    return $res;
}
