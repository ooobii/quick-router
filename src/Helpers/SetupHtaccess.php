<?php

namespace ooobii\QuickRouter\Helpers;

class SetupHtaccess {


    public const TAG_START = "# BEGIN QuickRouter Config (DO NOT EDIT & KEEP AT END!) -----";
    public const TAG_END   = "# END QuickRouter Config (DO NOT EDIT & KEEP AT END!) -------";

    public static function getFileContents() {
        $startTag = self::TAG_START;
        $endTag   = self::TAG_END;

        return <<< EOF
        $startTag
        RewriteEngine On
        RewriteBase /
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteRule ^(.+)$ index.php [QSA,L]
        $endTag 
        EOF;
    }

    /**
     * Writes the htaccess file to the project directory.
     */
    public static function createHtaccessFile() {

        //if an .htaccess file exists, we should only overwrite lines between the BEGIN and END tags.
        if (file_exists('.htaccess')) {
            self::updateHtAccessFile();

        } else {

            //if one doesn't exist, write a new .htaccess file to the project directory
            file_put_contents('.htaccess', self::getFileContents());

        }

        return TRUE;

    }

    /**
     * Updates the .htaccess file with the new configuration.
     * @return string The updated .htaccess file contents.
     */
    public static function updateHtAccessFile() {

        if(!file_exists('.htaccess')) {
            throw new \Exception("Unable to update .htaccess file. File does not exist.");
        }

        $content = trim(file_get_contents('.htaccess'));

        //find where config starts & ends
        $startPos = strpos($content, self::TAG_START);
        $endPos   = strpos($content, self::TAG_END);

        //if both config tags are not found, we can append the config to the end of the .htaccess file.
        if($startPos === FALSE && $endPos === FALSE) {

            $content .= PHP_EOL . self::getFileContents();

        } else {

            //if one or the other tag is not found, we need to throw an error.
            if($startPos === FALSE || $endPos === FALSE) {
                throw new \Exception("Unable to update .htaccess file. Config was not found in .htaccess file.");
            }

            //add tag length to end position.
            $endPos += strlen(self::TAG_END);

            //remove the old config
            $content = substr($content, 0, $startPos) . substr($content, $endPos);

            //if content doesn't have new line at end, add it.
            if(substr($content, -1) !== PHP_EOL) {
                $content .= PHP_EOL;
            }
    
            //add the new config
            $content .= self::getFileContents();

        }

        //overwrite the new config to the file
        file_put_contents('.htaccess', trim($content) . PHP_EOL);

    }


}