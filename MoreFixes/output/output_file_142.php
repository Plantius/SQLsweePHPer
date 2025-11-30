	function formatValue( $name, $value ) {
		$row = $this->mCurrentRow;

		$wiki = $row->files_dbname;

		switch ( $name ) {
			case 'files_timestamp':
				$formatted = htmlspecialchars( $this->getLanguage()->userTimeAndDate( $row->files_timestamp, $this->getUser() ) );
				break;
			case 'files_dbname':
				$formatted = $row->files_dbname;
				break;
			case 'files_url':
				$formatted = "<img src=\"{$row->files_url}\" style=\"width:135px;height:135px;\">";
				break;
			case 'files_name':
				$formatted = "<a href=\"{$row->files_page}\">{$row->files_name}</a>";
				break;
			case 'files_user':
				$formatted = "<a href=\"/wiki/Special:CentralAuth/{$row->files_user}\">{$row->files_user}</a>";
				break;
			default:
				$formatted = "Unable to format $name";
				break;
		}

		return $formatted;
	}
