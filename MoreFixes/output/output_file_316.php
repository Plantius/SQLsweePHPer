	protected function find_compatible_table_alias( $clause, $parent_query ) {
		$alias = false;

		// Sanity check. Only IN queries use the JOIN syntax.
		if ( ! isset( $clause['operator'] ) || 'IN' !== $clause['operator'] ) {
			return $alias;
		}

		// Since we're only checking IN queries, we're only concerned with OR relations.
		if ( ! isset( $parent_query['relation'] ) || 'OR' !== $parent_query['relation'] ) {
			return $alias;
		}

		$compatible_operators = array( 'IN' );

		foreach ( $parent_query as $sibling ) {
			if ( ! is_array( $sibling ) || ! $this->is_first_order_clause( $sibling ) ) {
				continue;
			}

			if ( empty( $sibling['alias'] ) || empty( $sibling['operator'] ) ) {
				continue;
			}

			// The sibling must both have compatible operator to share its alias.
			if ( in_array( strtoupper( $sibling['operator'] ), $compatible_operators, true ) ) {
				$alias = $sibling['alias'];
				break;
			}
		}

		return $alias;
	}
