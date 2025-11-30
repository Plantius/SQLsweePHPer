				$sql_having .= ($i == 0 ? '':' OR ') . '`' . implode('`.`', explode('.', $column['field_name'])) . '`' . ' LIKE "%' . $filter . '%"';
				$i++;
			}

			$sql_having .= ')';
		}

		return $sql_having;
	}
