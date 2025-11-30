    public function testHandleNonPrintableContents(
        $display_binary, $display_blob, $category, $content,
        $transformation_plugin, array $transform_options, $default_function,
        $meta, $url_params, $is_truncated, $output
    ) {
        $_SESSION['tmpval']['display_binary'] = $display_binary;
        $_SESSION['tmpval']['display_blob'] = $display_blob;
        $GLOBALS['cfg']['LimitChars'] = 50;
        $this->assertEquals(
            $output,
            $this->_callPrivateFunction(
                '_handleNonPrintableContents',
                array(
                    $category, $content, $transformation_plugin,
                    $transform_options, $default_function,
                    $meta, $url_params, &$is_truncated
                )
            )
        );
    }
