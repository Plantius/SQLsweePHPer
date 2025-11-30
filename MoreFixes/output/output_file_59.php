					db_execute("update graph_tree_items set order_key='$key' where id=" . $tree_item["id"]);

					$_tier = $tier;
				}
			}
		}
