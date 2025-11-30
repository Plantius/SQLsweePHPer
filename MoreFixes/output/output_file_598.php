	public static function onLoadExtensionSchemaUpdates( DatabaseUpdater $updater ) {
		$config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'globalnewfiles' );

		if ( $config->get( 'CreateWikiDatabase' ) === $config->get( 'DBname' ) ) {
			$updater->addExtensionTable(
				'gnf_files',
				__DIR__ . '/../sql/gnf_files.sql'
			);

			$updater->modifyExtensionField(
				'gnf_files',
				'files_timestamp',
				__DIR__ . '/../sql/patches/patch-gnf_files-binary.sql' 
			);

			$updater->modifyTable(
 				'gnf_files',
  				__DIR__ . '/../sql/patches/patch-gnf_files-add-indexes.sql',
				true
 			);
		}

		return true;
	}
