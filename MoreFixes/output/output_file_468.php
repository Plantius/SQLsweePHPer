	public function login(int $providerId, string $redirectUrl = null) {
		if ($this->userSession->isLoggedIn()) {
			return new RedirectResponse($redirectUrl);
		}
		if (!$this->isSecure()) {
			return $this->generateProtocolErrorResponse();
		}
		$this->logger->debug('Initiating login for provider with id: ' . $providerId);

		//TODO: handle exceptions
		$provider = $this->providerMapper->getProvider($providerId);

		$state = $this->random->generate(32, ISecureRandom::CHAR_DIGITS . ISecureRandom::CHAR_UPPER);
		$this->session->set(self::STATE, $state);
		$this->session->set(self::REDIRECT_AFTER_LOGIN, $redirectUrl);

		$nonce = $this->random->generate(32, ISecureRandom::CHAR_DIGITS . ISecureRandom::CHAR_UPPER);
		$this->session->set(self::NONCE, $nonce);

		$this->session->set(self::PROVIDERID, $providerId);
		$this->session->close();

		// get attribute mapping settings
		$uidAttribute = $this->providerService->getSetting($providerId, ProviderService::SETTING_MAPPING_UID, 'sub');
		$emailAttribute = $this->providerService->getSetting($providerId, ProviderService::SETTING_MAPPING_EMAIL, 'email');
		$displaynameAttribute = $this->providerService->getSetting($providerId, ProviderService::SETTING_MAPPING_DISPLAYNAME, 'name');
		$quotaAttribute = $this->providerService->getSetting($providerId, ProviderService::SETTING_MAPPING_QUOTA, 'quota');

		$claims = [
			// more details about requesting claims:
			// https://openid.net/specs/openid-connect-core-1_0.html#IndividualClaimsRequests
			'id_token' => [
				// ['essential' => true] means it's mandatory but it won't trigger an error if it's not there
				// null means we want it
				$emailAttribute => null,
				$displaynameAttribute => null,
				$quotaAttribute => null,
			],
			'userinfo' => [
				$emailAttribute => null,
				$displaynameAttribute => null,
				$quotaAttribute => null,
			],
		];

		if ($uidAttribute !== 'sub') {
			$claims['id_token'][$uidAttribute] = ['essential' => true];
			$claims['userinfo'][$uidAttribute] = ['essential' => true];
		}

		$extraClaimsString = $this->providerService->getSetting($providerId, ProviderService::SETTING_EXTRA_CLAIMS, '');
		if ($extraClaimsString) {
			$extraClaims = explode(' ', $extraClaimsString);
			foreach ($extraClaims as $extraClaim) {
				$claims['id_token'][$extraClaim] = null;
				$claims['userinfo'][$extraClaim] = null;
			}
		}

		$data = [
			'client_id' => $provider->getClientId(),
			'response_type' => 'code',
			'scope' => $provider->getScope(),
			'redirect_uri' => $this->urlGenerator->linkToRouteAbsolute(Application::APP_ID . '.login.code'),
			'claims' => json_encode($claims),
			'state' => $state,
			'nonce' => $nonce,
		];
		// pass discovery query parameters also on to the authentication
		$discoveryUrl = parse_url($provider->getDiscoveryEndpoint());
		if (isset($discoveryUrl["query"])) {
			$this->logger->debug('Add custom discovery query: ' . $discoveryUrl["query"]);
			$discoveryQuery = [];
			parse_str($discoveryUrl["query"], $discoveryQuery);
			$data += $discoveryQuery;
		}

		try {
			$discovery = $this->discoveryService->obtainDiscovery($provider);
		} catch (\Exception $e) {
			$this->logger->error('Could not reach provider at URL ' . $provider->getDiscoveryEndpoint());
			$response = new TemplateResponse('', 'error', [
				'errors' => [
					['error' => 'Could not the reach OpenID Connect provider.']
				]
			], TemplateResponse::RENDER_AS_ERROR);
			$response->setStatus(Http::STATUS_NOT_FOUND);
			return $response;
		}

		//TODO verify discovery

		$url = $discovery['authorization_endpoint'] . '?' . http_build_query($data);
		$this->logger->debug('Redirecting user to: ' . $url);

		// Workaround to avoid empty session on special conditions in Safari
		// https://github.com/nextcloud/user_oidc/pull/358
		if ($this->request->isUserAgent(['/Safari/']) && !$this->request->isUserAgent(['/Chrome/'])) {
			return new DataDisplayResponse('<meta http-equiv="refresh" content="0; url=' . $url . '" />');
		}

		return new RedirectResponse($url);
	}
