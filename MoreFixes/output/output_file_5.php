			$DB->query(
			    'SELECT DISTINCT (nvw.node_id)
                     FROM nv_webdictionary nvw
                     WHERE nvw.node_type = "product"
                       AND nvw.website = '.$website->id.'
                       AND nvw.text LIKE :text',
                'array',
                array(
                    ':text' => '%'.$text.'%'
                )
            );

			$dict_ids = $DB->result("node_id");

			// all columns to look for
			$cols[] = 'p.id LIKE ' .  protect('%'.$text.'%').' ';
			if(!empty($dict_ids))
            {
                $cols[] = 'p.id IN ('.implode(',', $dict_ids).')';
            }

			$where .= ' AND ( ';
			$where .= implode( ' OR ', $cols);
			$where .= ')';
		}
