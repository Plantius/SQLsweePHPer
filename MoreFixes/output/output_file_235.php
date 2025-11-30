function unzip_file($zip_archive, $archive_file, $zip_dir)
{
    if (!is_dir($zip_dir)) {
        LoggerManager::getLogger()->fatal('Specified directory for zip file extraction does not exist');
        if (defined('SUITE_PHPUNIT_RUNNER') || defined('SUGARCRM_INSTALL')) {
            return false;
        }
    }
    $zip = new ZipArchive;
    // We need realpath here for PHP streams support
    $res = $zip->open(UploadFile::realpath($zip_archive));

    if ($res !== true) {
        LoggerManager::getLogger()->fatal(sprintf('ZIP Error(%d): Status(%s)', $res, $zip->status));
        if (defined('SUITE_PHPUNIT_RUNNER') || defined('SUGARCRM_INSTALL')) {
            return false;
        }
    }

    if ($archive_file !== null) {
        $res = $zip->extractTo(UploadFile::realpath($zip_dir), $archive_file);
        if ((new SplFileInfo($archive_file))->getExtension() == 'php') {
            SugarCache::cleanFile(UploadFile::realpath($zip_dir).'/'.$archive_file);
        }
    } else {
        $res = $zip->extractTo(UploadFile::realpath($zip_dir));
        SugarCache::cleanDir(UploadFile::realpath($zip_dir));
    }

    if ($res !== true) {
        LoggerManager::getLogger()->fatal(sprintf('ZIP Error(%d): Status(%s)', $res, $zip->status));
        if (defined('SUITE_PHPUNIT_RUNNER') || defined('SUGARCRM_INSTALL')) {
            return false;
        }
    }

    return true;
}
