    private function dateIsInRequestFrame(\DateTime $date)
    {
        if (null === $date) {
            return false;
        }

        return (new \DateTime())->add(self::getResetInterval()) < $date->add(self::getRequestInterval());
    }
