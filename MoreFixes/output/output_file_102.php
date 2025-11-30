	function __construct() {
		$this->mDb = GlobalNewFilesHooks::getGlobalDB( DB_REPLICA, 'gnf_files' );

		if ( $this->getRequest()->getText( 'sort', 'files_date' ) == 'files_date' ) {
			$this->mDefaultDirection = IndexPager::DIR_DESCENDING;
		} else {
			$this->mDefaultDirection = IndexPager::DIR_ASCENDING;
		}

		parent::__construct( $this->getContext() );
	}
