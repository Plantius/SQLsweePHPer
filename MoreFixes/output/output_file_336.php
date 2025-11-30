	public function afterException($controller, $methodName, \Exception $exception) {
		if (get_class($controller) === PageController::class) {
			throw $exception;
		}

		if ($exception instanceof ConflictException) {
			if ($this->config->getSystemValue('loglevel', Util::WARN) === Util::DEBUG) {
				$this->logger->logException($exception);
			}
			return new JSONResponse([
				'status' => $exception->getStatus(),
				'message' => $exception->getMessage(),
				'data' => $exception->getData(),
			], $exception->getStatus());
		}
		
		if ($exception instanceof StatusException) {
			if ($this->config->getSystemValue('loglevel', Util::WARN) === Util::DEBUG) {
				$this->logger->logException($exception);
			}

			if ($controller instanceof OCSController) {
				$exception = new OCSException($exception->getMessage(), $exception->getStatus(), $exception);
				throw $exception;
			}
			return new JSONResponse([
				'status' => $exception->getStatus(),
				'message' => $exception->getMessage()
			], $exception->getStatus());
		}

		if (strpos(get_class($controller), 'OCA\\Deck\\Controller\\') === 0) {
			$response = [
				'status' => 500,
				'message' => $exception->getMessage()
			];
			$this->logger->logException($exception);
			if ($this->config->getSystemValue('debug', true) === true) {
				$response['exception'] = (array) $exception;
			}
			return new JSONResponse($response, 500);
		}

		// uncatched DoesNotExistExceptions will be thrown when the main entity is not found
		// we return a 403 so we don't leak information over existing entries
		// TODO: At some point those should properly be catched in the service classes
		if ($exception instanceof DoesNotExistException) {
			return new JSONResponse([
				'status' => 403,
				'message' => 'Permission denied'
			], 403);
		}

		throw $exception;
	}
