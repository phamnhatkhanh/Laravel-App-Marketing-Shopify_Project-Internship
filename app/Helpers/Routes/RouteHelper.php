<?php


if (!function_exists('includeRouteFiles')) {
    /**
     * Import file php in floder to specifical file.
     *
     * @param  string  $folder
     * @return void
     */
    function includeRouteFiles(string $folder)
    {
        $dirIterator = new \RecursiveDirectoryIterator($folder);
        /** @var \RecursiveDirectoryIterator | \RecursiveIteratorIterator $it */
        $it = new \RecursiveIteratorIterator($dirIterator);

        while ($it->valid()){
            if(!$it->isDot()
                && $it->isFile()
                && $it->isReadable()
                && $it->current()->getExtension() === 'php')
            {
                require $it->key();
            }
            $it->next();
        }
    }
}
