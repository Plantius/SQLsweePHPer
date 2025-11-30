    public function deleteCommentForShare($comment) {
        try {
            $q = Doctrine_Query::create()
                ->delete('Comment')
                ->where('id =' . $comment->getId());
            return $q->execute();
            // @codeCoverageIgnoreStart
        } catch (Exception $e) {
            throw new DaoException($e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
