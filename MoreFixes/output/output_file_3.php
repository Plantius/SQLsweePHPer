				$compatible_compares = array( '!=', 'NOT IN', 'NOT LIKE' );
			}

			$clause_compare  = strtoupper( $clause['compare'] );
			$sibling_compare = strtoupper( $sibling['compare'] );
			if ( in_array( $clause_compare, $compatible_compares, true ) && in_array( $sibling_compare, $compatible_compares, true ) ) {
				$alias = $sibling['alias'];
				break;
			}
		}
