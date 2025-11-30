				$row_rub = sql_fetsel("id_rubrique", "spip_rubriques",
					"lang='" . $GLOBALS['spip_lang'] . "' AND id_parent=$id_parent");
				if ($row_rub) {
					$row['id_rubrique'] = $row_rub['id_rubrique'];
				}
			}
		}
	}

	return $row;
}
