    public function getLatestRevisions()
    {
        if (! $this->latest_revisions) {
            $pm = ProjectManager::instance();
            $project = $pm->getProject($this->group_id);
            if ($project && $this->canBeUsedByProject($project)) {
                list($this->latest_revisions,) = svn_get_revisions($project, 0, 5, '', '', '', '', 0, false);
            }
        }
        return $this->latest_revisions;
    }
