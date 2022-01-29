#!/usr/bin/env php
<?php

/*
 * Compares manifests from two zip packages.
 * Returns exit code 0 if they are the same, 1 if they differ.
 * Usage: ./diff-pkgs.php <zip-file1> <zip-file2>
 */

require_once __DIR__ . '/shared.php';

try {
    $manifest1 = loadManifest($argv[1]);
    $manifest2 = loadManifest($argv[2]);
    if (deepCompare($manifest1, $manifest2)) {
        exit(0); // emulate diff - 0, files are the same
    } else {
        exit(1); // emulate diff - 1, files differ
    }
} catch (Exception $e) {
    $msg = $e->getMessage();
    echo "Error: ", $msg, "\n";
    exit(2);
}
