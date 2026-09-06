<?php

if (strpos(strtolower($_SERVER['PHP_SELF']), 'zip-compat.php') !== false) {
    die('This file can not be used on its own!');
}

/*
 * Theme Studio updates should have the same ZIP requirements as Geeklog's
 * plugin installer. Geeklog 2.1.x falls back to PEAR Archive_Zip when the PHP
 * ZipArchive extension is not installed. Eclipse only needs a small read-only
 * subset of the ZipArchive API, so expose that subset on the administration
 * dashboard and delegate it to the same Archive_Zip backend.
 *
 * Keep this compatibility class scoped to admin/index.php. Defining a global
 * ZipArchive replacement on every request could make unrelated Geeklog code
 * believe that the native PHP extension is installed.
 */
$eclipseZipCompatScript = isset($_SERVER['PHP_SELF']) ? str_replace('\\', '/', strtolower((string) $_SERVER['PHP_SELF'])) : '';
$eclipseZipCompatDashboard = preg_match('#(?:^|/)admin/index\.php$#', $eclipseZipCompatScript) === 1;

if ($eclipseZipCompatDashboard && !class_exists('ZipArchive', false)) {
    define('ECLIPSE_ZIP_USES_GEEKLOG_BACKEND', true);

    class ZipArchive
    {
        public $numFiles = 0;

        private $archive = null;
        private $contents = array();
        private $temporaryRoot = '';

        public function open($filename, $flags = null)
        {
            if (!is_string($filename) || $filename === '' || !is_file($filename)) {
                return false;
            }

            if (!class_exists('Archive_Zip', false)) {
                @include_once 'Archive/Zip.php';
            }
            if (!class_exists('Archive_Zip', false)) {
                return false;
            }

            $this->archive = new Archive_Zip($filename);
            $list = @$this->archive->listContent();
            if (!is_array($list) || count($list) === 0) {
                $this->archive = null;
                return false;
            }

            $this->contents = array_values($list);
            $this->numFiles = count($this->contents);
            return true;
        }

        public function getNameIndex($index)
        {
            if (!isset($this->contents[$index]) || !is_array($this->contents[$index])) {
                return false;
            }
            return isset($this->contents[$index]['filename']) ? $this->contents[$index]['filename'] : false;
        }

        public function statIndex($index)
        {
            if (!isset($this->contents[$index]) || !is_array($this->contents[$index])) {
                return false;
            }
            $entry = $this->contents[$index];
            return array(
                'name' => isset($entry['filename']) ? $entry['filename'] : '',
                'size' => isset($entry['size']) ? (int) $entry['size'] : 0,
                'comp_size' => isset($entry['compressed_size']) ? (int) $entry['compressed_size'] : (isset($entry['compressed']) ? (int) $entry['compressed'] : 0),
                'comp_method' => isset($entry['compression']) ? $entry['compression'] : 0,
            );
        }

        public function extractTo($destination, $entries = null)
        {
            if (!$this->archive || !is_string($destination) || $destination === '') {
                return false;
            }
            if (!is_dir($destination) && !@mkdir($destination, 0750, true)) {
                return false;
            }
            if (!is_writable($destination)) {
                return false;
            }

            $options = array('add_path' => $destination);
            if ($entries !== null) {
                $options['by_name'] = is_array($entries) ? $entries : array($entries);
            }

            $result = @$this->archive->extract($options);
            return $result !== false && $result !== 0;
        }

        public function getStream($name)
        {
            global $_CONF;

            if (!$this->archive || !is_string($name) || $name === '') {
                return false;
            }

            $normalized = str_replace('\\', '/', $name);
            if ($normalized[0] === '/' || strpos($normalized, "\0") !== false || preg_match('#(^|/)\.\.(/|$)#', $normalized)) {
                return false;
            }

            $known = false;
            foreach ($this->contents as $entry) {
                if (isset($entry['filename']) && str_replace('\\', '/', $entry['filename']) === $normalized) {
                    $known = true;
                    break;
                }
            }
            if (!$known) {
                return false;
            }

            if ($this->temporaryRoot === '') {
                $base = !empty($_CONF['path_data']) ? rtrim((string) $_CONF['path_data'], '/\\') : sys_get_temp_dir();
                $this->temporaryRoot = $base . DIRECTORY_SEPARATOR . 'eclipse-zip-' . substr(sha1(uniqid('', true)), 0, 12);
                if (!@mkdir($this->temporaryRoot, 0750, true)) {
                    $this->temporaryRoot = '';
                    return false;
                }
            }

            if (!$this->extractTo($this->temporaryRoot, array($name))) {
                return false;
            }

            $candidate = $this->temporaryRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalized);
            if (is_link($candidate) || !is_file($candidate)) {
                return false;
            }

            $rootReal = realpath($this->temporaryRoot);
            $fileReal = realpath($candidate);
            if ($rootReal === false || $fileReal === false) {
                return false;
            }
            $rootPrefix = rtrim($rootReal, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            if (strpos($fileReal, $rootPrefix) !== 0) {
                return false;
            }

            return @fopen($fileReal, 'rb');
        }

        public function close()
        {
            $this->archive = null;
            $this->contents = array();
            $this->numFiles = 0;
            if ($this->temporaryRoot !== '' && is_dir($this->temporaryRoot)) {
                $this->removeTree($this->temporaryRoot);
            }
            $this->temporaryRoot = '';
            return true;
        }

        private function removeTree($path)
        {
            if (!is_dir($path) || is_link($path)) {
                if (file_exists($path) || is_link($path)) @unlink($path);
                return;
            }
            $items = @scandir($path);
            if (is_array($items)) {
                foreach ($items as $item) {
                    if ($item === '.' || $item === '..') continue;
                    $child = $path . DIRECTORY_SEPARATOR . $item;
                    if (is_dir($child) && !is_link($child)) {
                        $this->removeTree($child);
                    } else {
                        @unlink($child);
                    }
                }
            }
            @rmdir($path);
        }
    }
}
