	public function import($block, $sanitize = TRUE)
	{
		$this->CI->load->helper('file');
		
		$model = $this->model();
		if (!is_numeric($block))
		{
			$block_data = $model->find_by_name($block, 'array');
		}
		else
		{
			$block_data = $model->find_by_key($block, 'array');
		}
		
		$view_twin = APPPATH.'views/_blocks/'.$block_data['name'].EXT;

		$output = '';
		if (file_exists($view_twin))
		{
			$view_twin_info = get_file_info($view_twin);
			
			$tz = date('T');
			if ($view_twin_info['date'] > strtotime($block_data['last_modified'].' '.$tz) OR
				$block_data['last_modified'] == $block_data['date_added'])
			{
				// must have content in order to not return error
				$output = file_get_contents($view_twin);

				// replace PHP tags with template tags... comments are replaced because of xss_clean()
				if ($sanitize)
				{
					$output = php_to_template_syntax($output);
				}
			}
		}
		return $output;
	}
