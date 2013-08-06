#!/bin/sh
dir=$(dirname "$0")
php -d "include_path=$dir/lib" "$dir/bin/jafar" "$dir"
