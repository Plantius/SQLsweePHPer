    public function getMostCommentedShares($shareCount) {
        try {
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            $result = $q->execute(
                "SELECT share_id, COUNT( share_id ) AS no_of_comments
                FROM ohrm_buzz_comment
                GROUP BY share_id
                ORDER BY no_of_comments DESC 
                LIMIT " . $shareCount
            );
            return $result->fetchAll();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
