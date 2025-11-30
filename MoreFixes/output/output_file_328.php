	protected function parseChunk() {
		if (empty($this->buffer)) return false;
		$s = $this->seek();

		// setting a property
		if ($this->keyword($key) && $this->assign() &&
			$this->propertyValue($value, $key) && $this->end())
		{
			$this->append(array('assign', $key, $value), $s);
			return true;
		} else {
			$this->seek($s);
		}


		// look for special css blocks
		if ($this->literal('@', false)) {
			$this->count--;

			// media
			if ($this->literal('@media')) {
				if (($this->mediaQueryList($mediaQueries) || true)
					&& $this->literal('{'))
				{
					$media = $this->pushSpecialBlock("media");
					$media->queries = is_null($mediaQueries) ? array() : $mediaQueries;
					return true;
				} else {
					$this->seek($s);
					return false;
				}
			}

			if ($this->literal("@", false) && $this->keyword($dirName)) {
				if ($this->isDirective($dirName, $this->blockDirectives)) {
					if (($this->openString("{", $dirValue, null, array(";")) || true) &&
						$this->literal("{"))
					{
						$dir = $this->pushSpecialBlock("directive");
						$dir->name = $dirName;
						if (isset($dirValue)) $dir->value = $dirValue;
						return true;
					}
				} elseif ($this->isDirective($dirName, $this->lineDirectives)) {
					if ($this->propertyValue($dirValue) && $this->end()) {
						$this->append(array("directive", $dirName, $dirValue));
						return true;
					}
				}
			}

			$this->seek($s);
		}

		// setting a variable
		if ($this->variable($var) && $this->assign() &&
			$this->propertyValue($value) && $this->end())
		{
			$this->append(array('assign', $var, $value), $s);
			return true;
		} else {
			$this->seek($s);
		}

		if ($this->import($importValue)) {
			$this->append($importValue, $s);
			return true;
		}

		// opening parametric mixin
		if ($this->tag($tag, true) && $this->argumentDef($args, $isVararg) &&
			($this->guards($guards) || true) &&
			$this->literal('{'))
		{
			$block = $this->pushBlock($this->fixTags(array($tag)));
			$block->args = $args;
			$block->isVararg = $isVararg;
			if (!empty($guards)) $block->guards = $guards;
			return true;
		} else {
			$this->seek($s);
		}

		// opening a simple block
		if ($this->tags($tags) && $this->literal('{')) {
			$tags = $this->fixTags($tags);
			$this->pushBlock($tags);
			return true;
		} else {
			$this->seek($s);
		}

		// closing a block
		if ($this->literal('}', false)) {
			try {
				$block = $this->pop();
			} catch (exception $e) {
				$this->seek($s);
				$this->throwError($e->getMessage());
			}

			$hidden = false;
			if (is_null($block->type)) {
				$hidden = true;
				if (!isset($block->args)) {
					foreach ($block->tags as $tag) {
						if (!is_string($tag) || $tag{0} != $this->lessc->mPrefix) {
							$hidden = false;
							break;
						}
					}
				}

				foreach ($block->tags as $tag) {
					if (is_string($tag)) {
						$this->env->children[$tag][] = $block;
					}
				}
			}

			if (!$hidden) {
				$this->append(array('block', $block), $s);
			}

			// this is done here so comments aren't bundled into he block that
			// was just closed
			$this->whitespace();
			return true;
		}

		// mixin
		if ($this->mixinTags($tags) &&
			($this->argumentDef($argv, $isVararg) || true) &&
			($this->keyword($suffix) || true) && $this->end())
		{
			$tags = $this->fixTags($tags);
			$this->append(array('mixin', $tags, $argv, $suffix), $s);
			return true;
		} else {
			$this->seek($s);
		}

		// spare ;
		if ($this->literal(';')) return true;

		return false; // got nothing, throw error
	}
