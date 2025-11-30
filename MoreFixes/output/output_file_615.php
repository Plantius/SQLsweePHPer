    public static function updatePassword(Db $zdb, $id_adh, $pass)
    {
        try {
            $cpass = password_hash($pass, PASSWORD_BCRYPT);

            $update = $zdb->update(self::TABLE);
            $update->set(
                array('mdp_adh' => $cpass)
            )->where(self::PK . ' = ' . $id_adh);
            $zdb->execute($update);
            Analog::log(
                'Password for `' . $id_adh . '` has been updated.',
                Analog::DEBUG
            );
            return true;
        } catch (Throwable $e) {
            Analog::log(
                'An error occurred while updating password for `' . $id_adh .
                '` | ' . $e->getMessage(),
                Analog::ERROR
            );
            throw $e;
        }
    }
