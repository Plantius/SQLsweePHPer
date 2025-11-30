						WHERE public = 1 And id IN (' . ze\escape::in($categoryIds) . ')';
				}
				else{
								
					$sql = '
						SELECT id,name,parent_id
						FROM ' . DB_PREFIX . 'categories
						WHERE id IN (' . ze\escape::in($categoryIds) . ')';
				}
				$result = ze\sql::select($sql);
			
				while ($row = ze\sql::fetchAssoc($result)) {
					$currentParentId = false;
					$categoryName = [];
					$categoryName[] = $row['name'];
				
					if ($row['parent_id']) {
						$currentParentId = $row['parent_id'];
						while ($currentParentId) {
							$parent = ze\row::get('categories',['id','name','parent_id'],['id' => $currentParentId]);
							if ($parent) {
								$categoryName[] = $parent['name'];
								$currentParentId = $parent['parent_id'];
							} else {
								$currentParentId = false;
							}
						}
					}
				
					krsort($categoryName);
					$categoryName = implode (" / ", $categoryName);
					$allCurrentlySelectedCategories[] = $categoryName;
				}
			    If($this->setting('show_category_name') && ze::setting('enable_display_categories_on_content_lists')){
				 		$inner['All_Currently_Selected_Categories'] = $allCurrentlySelectedCategories;	
				}
				else{
						$inner['All_Currently_Selected_Categories'] = "";

				}
			}
		}
		
		$this->framework('Outer', $outer, $inner);
	}
