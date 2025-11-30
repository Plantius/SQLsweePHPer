    public function sendEmailAction(Request $request, $generateNewKey = true)
    {
        try {
            /** @var UserInterface $user */
            $user = $this->findUser($request->get('user'));
            if (true === $generateNewKey) {
                $this->generateTokenForUser($user);
            }
            $email = $this->getEmail($user);
            $this->sendTokenEmail($user, $this->getSenderAddress($request), $email);
            $response = new JsonResponse(['email' => $email]);
        } catch (EntityNotFoundException $ex) {
            $response = new JsonResponse($ex->toArray(), 400);
        } catch (TokenAlreadyRequestedException $ex) {
            $response = new JsonResponse($ex->toArray(), 400);
        } catch (NoTokenFoundException $ex) {
            $response = new JsonResponse($ex->toArray(), 400);
        } catch (TokenEmailsLimitReachedException $ex) {
            $response = new JsonResponse($ex->toArray(), 400);
        } catch (EmailTemplateException $ex) {
            $response = new JsonResponse($ex->toArray(), 400);
        } catch (UserNotInSystemException $ex) {
            $response = new JsonResponse($ex->toArray(), 400);
        }

        return $response;
    }
