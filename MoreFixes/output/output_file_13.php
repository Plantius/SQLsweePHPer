			$id = db_fetch_cell("SELECT hex FROM colors WHERE hex='$hex'");

			if (!empty($id)) {
				db_execute("UPDATE colors SET name='$name', read_only='on' WHERE hex='$hex'");
			} else {
				db_execute("INSERT INTO colors (name, hex, read_only) VALUES ('$name', '$hex', 'on')");
			}
		}
	}

	return true;
}
