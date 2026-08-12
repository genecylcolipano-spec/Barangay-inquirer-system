<?php

/**
 * Normalize a file path to prevent directory traversal attacks
 * 
 * @param string $path The path to normalize
 * @return string The normalized path
 */
if (! function_exists('normalize_path')) {
    function normalize_path($path)
    {
        // Remove null bytes
        $path = str_replace("\0", '', $path);
        
        // Convert backslashes to forward slashes
        $path = str_replace('\\', '/', $path);
        
        // Remove leading slashes
        $path = ltrim($path, '/');
        
        // Resolve . and .. references
        $parts = [];
        foreach (explode('/', $path) as $part) {
            if ($part === '' || $part === '.') {
                continue;
            }
            if ($part === '..') {
                array_pop($parts);
                continue;
            }
            $parts[] = $part;
        }
        
        return implode('/', $parts);
    }
}
