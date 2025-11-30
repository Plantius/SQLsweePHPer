    public function getMostLikedShares($shareCount) {
        try {
            $q = Doctrine_Manager::getInstance()->getCurrentConnection();
            $result = $q->execute(
                "SELECT  share_id , COUNT(share_id) AS  no_of_likes 
                FROM  ohrm_buzz_like_on_share 
                GROUP BY  share_id 
                ORDER BY  no_of_likes DESC 
                LIMIT " . $shareCount
            );
            return $result->fetchAll();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
