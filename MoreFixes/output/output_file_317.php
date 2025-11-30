	protected function fixTags($tags) {
		// move @ tags out of variable namespace
		foreach ($tags as &$tag) {
			if ($tag{0} == $this->lessc->vPrefix)
				$tag[0] = $this->lessc->mPrefix;
		}
		return $tags;
	}
