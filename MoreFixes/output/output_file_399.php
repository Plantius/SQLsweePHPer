	public function get_items( $request ) {

		$sked = get_template_sked( $request['post_id'] );

		return new WP_REST_Response( $sked, 200 );

	}
