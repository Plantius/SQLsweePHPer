    private function getLangFolderForEdit()
    {
        $langFiles = array();
        $files = rreadDir('application' . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . '' . $_GET['editLang'] . DIRECTORY_SEPARATOR);
        $arrPhpFiles = $arrJsFiles = array();
        foreach ($files as $ext => $filesLang) {
            foreach ($filesLang as $fileLang) {
                if ($ext == 'php') {
                    require $fileLang;
                    if (isset($lang)) {
                        $arrPhpFiles[$fileLang] = $lang;
                        unset($lang);
                    }
                }
                if ($ext == 'js') {
                    $jsTrans = file_get_contents($fileLang);
                    preg_match_all('/(.+?)"(.+?)"/', $jsTrans, $PMA);
                    $arrJsFiles[$fileLang] = $PMA;
                    unset($PMA);
                }
            }
        }
        $langFiles[0] = $arrPhpFiles;
        $langFiles[1] = $arrJsFiles;
        return $langFiles;
    }
