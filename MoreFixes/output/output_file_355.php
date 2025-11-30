	public function __construct($form_header = '', $form_action = '', $form_id = '', $form_width,
		$session_var = '', $action_url = '', $action_label = '') {
		global $item_rows;

		$this->form_header   = $form_header;
		$this->form_action   = $form_action;
		$this->form_id       = $form_id;
		$this->form_width    = $form_width;
		$this->action_url    = $action_url;
		$this->action_label  = $action_label;
		$this->session_var   = $session_var;
		$this->item_rows     = $item_rows;

		if ($this->action_url != '' && $this->action_label == '') {
			$this->action_label = __('Add');
		}

		/* default filter */
		$this->default_filter = array(
			'rows' => array(
				'row1' => array(
					'filter' => array(
						'friendly_name'  => __('Search'),
						'filter'         => FILTER_CALLBACK,
						'filter_options' => array('options' => 'sanitize_search_string'),
						'placeholder'    => __('Enter a search term'),
						'size'           => '30',
						'default'        => '',
						'pageset'        => true,
						'max_length'     => '120'
					),
					'rows' => array(
						'friendly_name' => __('Rows'),
						'filter'        => FILTER_VALIDATE_INT,
						'method'        => 'drop_array',
						'default'       => '-1',
						'pageset'       => true,
						'array'         => $this->item_rows
					),
					'go' => array(
						'display' => __('Go'),
						'title'   => __('Apply filter to table'),
						'method'  => 'submit',
					),
					'clear' => array(
						'display' => __('Clear'),
						'title'   => __('Reset filter to default values'),
						'method'  => 'button',
					)
				)
			),
			'sort' => array(
				'sort_column' => 'name',
				'sort_direction' => 'ASC'
			)
		);
	}
