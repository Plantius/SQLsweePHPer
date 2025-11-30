    public function scopeSearch(Builder $query, array $search = [])
    {
        if (empty($search)) {
            return $query;
        }

        if (!array_intersect(array_keys($search), $this->searchable)) {
            return $query;
        }

        return $query->where($search);
    }
