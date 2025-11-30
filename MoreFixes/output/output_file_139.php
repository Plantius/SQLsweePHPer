	function execute( $par ) {
		$this->setHeaders();
		$this->outputHeader();

		$pager = new GlobalNewFilesPager();
		
		$this->getOutput()->addParserOutputContent( $pager->getFullOutput() );
	}
