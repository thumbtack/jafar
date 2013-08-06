<?php

namespace jafar;

interface Filesystem {
    public function enumerate($path);
}

class RealFilesystem implements Filesystem {
    public function enumerate($path) {
        return new \DirectoryIterator($path);
    }
}

function discover_test_files(Filesystem $fs, array $paths) {
    $test_files = [];

    if (!$paths) {
        $paths = ['.'];
    }

    foreach ($paths as $path) {
        if (is_file($path)) {
            $test_files[] = $path;
        } else if (is_dir($path)) {
            $test_files = array_merge($test_files, collect_files($fs, $path));
        } else {
            throw new \InvalidArgumentException("$path does not exist.");
        }
    }

    return $test_files;
}

function collect_files(Filesystem $fs, $path, $already_inside_test_dir=false) {
    $basename = basename($path);
    $is_test_dir = $already_inside_test_dir || preg_match('/^(test|spec)/', $basename);
    $pattern = '/' . ($is_test_dir ? '' : '(_test|_spec|Test|Spec)') . '\.php$/';

    $test_files = [];

    foreach ($fs->enumerate($path) as $file) {
        if ($file->isDot()) {
            continue;
        }

        if ($file->isFile() && preg_match($pattern, $file->getBasename())) {
            $test_files[] = $file->getPathname();
        } else if ($file->isDir()) {
            $test_files = array_merge(
                $test_files,
                collect_files($fs, $file->getPathname(), $is_test_dir)
            );
        }
    }

    return $test_files;
}
