    public function getRawRevisionsAndCount($limit, PFUser $author)
    {
        return svn_get_revisions(
            $this->project,
            0,
            $limit,
            '',
            $author->getUserName(),
            '',
            '',
            0,
            false
        );
    }
