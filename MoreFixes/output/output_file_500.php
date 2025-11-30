    public function resetAction(Request $request)
    {
        try {
            $token = $request->get('token');
            /** @var UserInterface $user */
            $user = $this->findUserByValidToken($token);
            $this->changePassword($user, $request->get('password', ''));
            $this->deleteToken($user);
            $this->loginUser($user, $request);
            $response = new JsonResponse(['user' => $user->getUsername()]);
        } catch (InvalidTokenException $ex) {
            $response = new JsonResponse($ex->toArray(), 400);
        } catch (MissingPasswordException $ex) {
            $response = new JsonResponse($ex->toArray(), 400);
        }

        return $response;
    }
