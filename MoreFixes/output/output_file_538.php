    public function testGetDataCellForNonNumericColumns(
        $protectBinary, $column, $class, $meta, $map,
        $_url_params, $condition_field, $transformation_plugin,
        $default_function, array $transform_options, $is_field_truncated,
        $analyzed_sql_results, $dt_result, $col_index, $output
    ) {
        $_SESSION['tmpval']['display_binary'] = true;
        $_SESSION['tmpval']['display_blob'] = false;
        $_SESSION['tmpval']['relational_display'] = false;
        $GLOBALS['cfg']['LimitChars'] = 50;
        $GLOBALS['cfg']['ProtectBinary'] = $protectBinary;
        $this->assertEquals(
            $output,
            $this->_callPrivateFunction(
                '_getDataCellForNonNumericColumns',
                array(
                    $column, $class, $meta, $map, $_url_params, $condition_field,
                    $transformation_plugin, $default_function, $transform_options,
                    $is_field_truncated, $analyzed_sql_results, &$dt_result, $col_index
                )
            )
        );
    }
