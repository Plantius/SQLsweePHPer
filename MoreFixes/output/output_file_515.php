	public function settings( $settings, $options ) {
		$settings['zerospam_info'] = array(
			'section' => 'zerospam',
			'type'    => 'html',
			'html'    => sprintf(
				wp_kses(
					/* translators: %1s: Replaced with the Zero Spam URL, %2$s: Replaced with the DDoD attack wiki URL */
					__( '<h3 style="margin-top: 0">Enabling enhanced protection is highly recommended.</h3><p>Enhanced protection adds additional checks using one of the largest, most comprehensive, constantly-growing global malicious IP, email, and username databases available, the  <a href="%1$s" target="_blank" rel="noopener noreferrer">Zero Spam Blacklist</a>. Once enabled, all visitors will be checked against this blacklist that includes protected forms containing email and username fields &mdash; giving you the peace of mind that submissions are coming from legitimate. It can also help prevent <a href="%2$s" target="_blank" rel="noopener noreferrer">DDoS attacks</a> &amp; fraudsters looking to test stolen credit card numbers.</p>', 'zerospam' ),
					array(
						'h3'     => array(
							'style' => array(),
						),
						'p'      => array(),
						'a'      => array(
							'href'  => array(),
							'class' => array(),
						),
						'strong' => array(),
					)
				),
				esc_url( ZEROSPAM_URL . '?utm_source=' . site_url() . '&utm_medium=admin_zerospam_info&utm_campaign=wpzerospam' ),
				esc_url( 'https://en.wikipedia.org/wiki/Denial-of-service_attack' )
			),
		);

		$settings['zerospam'] = array(
			'title'       => __( 'Status', 'zerospam' ),
			'section'     => 'zerospam',
			'type'        => 'checkbox',
			'options'     => array(
				'enabled' => __( 'Enabled', 'zerospam' ),
			),
			'desc'        => sprintf(
				wp_kses(
					/* translators: %s: Replaced with the Zero Spam URL */
					__( 'Blocks visitor IPs, email addresses &amp; usernames that have been reported to <a href="%s" target="_blank" rel="noopener noreferrer">Zero Spam</a>.', 'zerospam' ),
					array(
						'strong' => array(),
						'a'      => array(
							'target' => array(),
							'href'   => array(),
							'rel'    => array(),
						),
					)
				),
				esc_url( ZEROSPAM_URL . '?utm_source=wordpresszerospam&utm_medium=admin_link&utm_campaign=wordpresszerospam' )
			),
			'value'       => ! empty( $options['zerospam'] ) ? $options['zerospam'] : false,
			'recommended' => 'enabled',
		);

		$settings['zerospam_license'] = array(
			'title'       => __( 'License Key', 'zerospam' ),
			'desc'        => sprintf(
				wp_kses(
					/* translators: %1$s: Replaced with the Zero Spam URL, %2$s: Replaced with the Zero Spam subscription URL */
					__( 'Enter your <a href="%1$s" target="_blank" rel="noopener noreferrer">Zero Spam</a> license key or define it in <code>wp-config.php</code>, using the constant <code>ZEROSPAM_LICENSE_KEY</code> to enable enhanced protection. Don\'t have an license key? <a href="%2$s" target="_blank" rel="noopener noreferrer"><strong>Get one now!</strong></a>', 'zerospam' ),
					array(
						'strong' => array(),
						'a'      => array(
							'target' => array(),
							'href'   => array(),
							'rel'    => array(),
						),
						'code'   => array(),
					)
				),
				esc_url( ZEROSPAM_URL ),
				esc_url( ZEROSPAM_URL . 'product/premium/' )
			),
			'section'     => 'zerospam',
			'type'        => 'text',
			'field_class' => 'regular-text',
			'placeholder' => __( 'Enter your Zero Spam license key.', 'zerospam' ),
			'value'       => ! empty( $options['zerospam_license'] ) ? $options['zerospam_license'] : false,
		);

		if ( defined( 'ZEROSPAM_LICENSE_KEY' ) && ! $settings['zerospam_license']['value'] ) {
			$settings['zerospam_license']['value'] = ZEROSPAM_LICENSE_KEY;
		}

		$settings['zerospam_timeout'] = array(
			'title'       => __( 'API Timeout', 'zerospam' ),
			'section'     => 'zerospam',
			'type'        => 'number',
			'field_class' => 'small-text',
			'suffix'      => __( 'seconds', 'zerospam' ),
			'placeholder' => __( '5', 'zerospam' ),
			'min'         => 0,
			'desc'        => __( 'Setting to high could result in degraded site performance, too low won\'t allow to API enough time to respond; recommended 5 seconds.', 'zerospam' ),
			'value'       => ! empty( $options['zerospam_timeout'] ) ? $options['zerospam_timeout'] : 5,
			'recommended' => 5,
		);

		$settings['zerospam_cache'] = array(
			'title'       => __( 'Cache Expiration', 'zerospam' ),
			'section'     => 'zerospam',
			'type'        => 'number',
			'field_class' => 'small-text',
			'suffix'      => __( 'day(s)', 'zerospam' ),
			'placeholder' => WEEK_IN_SECONDS,
			'min'         => 0,
			'desc'        => __( 'Setting to high could result in outdated information, too low could cause a decrease in performance; recommended 14 days.', 'zerospam' ),
			'value'       => ! empty( $options['zerospam_cache'] ) ? $options['zerospam_cache'] : 14,
			'recommended' => 14,
		);

		$settings['zerospam_confidence_min'] = array(
			'title'       => __( 'Confidence Minimum', 'zerospam' ),
			'section'     => 'zerospam',
			'type'        => 'number',
			'field_class' => 'small-text',
			'suffix'      => __( '%', 'zerospam' ),
			'placeholder' => __( '30', 'zerospam' ),
			'min'         => 0,
			'max'         => 100,
			'step'        => 0.1,
			'desc'        => sprintf(
				wp_kses(
					/* translators: %s: Replaced with the Zero Spam API URL */
					__( 'Minimum <a href="%s" target="_blank" rel="noopener noreferrer">confidence score</a> an IP must meet before being blocked. Setting this too low could cause users to be blocked that shouldn\'t be; recommended 20%%.', 'zerospam' ),
					array(
						'a' => array(
							'target' => array(),
							'href'   => array(),
							'rel'    => array(),
						),
					)
				),
				esc_url( ZEROSPAM_URL . 'spam-blacklist-api/?utm_source=' . site_url() . '&utm_medium=admin_confidence_score&utm_campaign=wpzerospam' )
			),
			'value'       => ! empty( $options['zerospam_confidence_min'] ) ? $options['zerospam_confidence_min'] : 30,
			'recommended' => 30,
		);

		return $settings;
	}
