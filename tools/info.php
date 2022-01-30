#!/usr/bin/env php
<?php

/*
 * Compares manifests from two zip packages.
 * Returns exit code 0 if they are the same, 1 if they differ.
 * Usage: ./diff-pkgs.php <zip-file1> <zip-file2>
 */

require_once __DIR__ . '/shared.php';

try {
    array_shift($argv);
    $files = preprocessFileArgs($argv);

    $manifests = [];
    foreach ($files as $file) {
        $basename = basename($file);
        if (array_key_exists($basename, $manifests)) {
            throw new RuntimeException("File name collision ($basename).");
        }
        $manifests[$basename] = loadManifest($file);
    }
    ksort($manifests);

    $pipelineFiles = [];
    foreach ($manifests as $file => $manifest) {
        foreach ($manifest['pipelines'] as $pipeline) {
            $name = $pipeline['name'];
            if (!array_key_exists($name, $pipelineFiles)) {
                $pipelineFiles[$name] = [];
            }
            $pipelineFiles[$name][] = $file;
        }
    }

    foreach ($manifests as $file => $manifest) {
        echo $file, "\n";
        echo "\tID: ", $manifest['runtime']['id'], "\n";
        echo "\tName: ", $manifest['runtime']['longName'], "\n";
        echo "\tDescription: ", $manifest['runtime']['description'], "\n";

        $depFiles = array_map(function ($pipeline) use ($pipelineFiles) {
            return $pipelineFiles[$pipeline['name']];
        }, $manifest['pipelines']);
        $depFiles = mergeNameSets($depFiles, $file);

        if ($depFiles) {
            echo "\tFiles that share some of the pipelines:\n";
            foreach ($depFiles as $f) {
                echo "\t\t$f\n";
            }
        }
        echo "\n";
    }
} catch (Exception $e) {
    $msg = $e->getMessage();
    echo "Error: ", $msg, "\n";
    exit(1);
}
