    public function newpassword() {

        if ($token = $this->param('token')) {

            $user = $this->app->storage->findOne('cockpit/accounts', ['_reset_token' => $token]);

            if (!$user) {
                return false;
            }

            $user['md5email'] = md5($user['email']);

            return $this->render('cockpit:views/layouts/newpassword.php', compact('user', 'token'));
        }

        return false;

    }
