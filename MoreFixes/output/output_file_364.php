    public function dataTestSpacing()
    {
        $i = 1;
        /*
                    * Code
                    * result
                    * test name
                    * test number
                    */
        return array(array("{include 'simple_function_lib.tpl'}A{call name='simple' bar=\$foo}C", "AbarC", 'T1', $i++),
                     array("{include 'simple_function_lib.tpl'}A\n{call name='simple' bar=\$foo}C", "A\nbarC", 'T2', $i++),
                     array("{include 'simple_function_lib.tpl'}A\n{call name='simple' bar=\$foo}\nC", "A\nbar\nC", 'T3', $i++),
                     array("{include 'simple_function_lib.tpl'}A\n{call name='simple' bar=\$foo}\nC", "A\nbar\nC", 'T4', $i++),
                     array("{include 'simple_function_lib.tpl'}A\n\n{call name='simple' bar=\$foo}\n\nC", "A\n\nbar\n\nC", 'T5', $i++),
                     array("{function name=simple}{\$bar}{/function}{call name='simple' bar=\$foo}", "bar", 'T6', $i++),
                     array("{function name=simple}A{\$bar}C{/function}{call name='simple' bar=\$foo}", "AbarC", 'T7', $i++),
                     array("{function name=simple}A\n{\$bar}C{/function}{call name='simple' bar=\$foo}", "A\nbarC", 'T8', $i++),
                     array("{function name=simple}A{\$bar}\nC{/function}{call name='simple' bar=\$foo}", "Abar\nC", 'T9', $i++),
                     array("{function name=simple}A\n{\$bar}\nC{/function}{call name='simple' bar=\$foo}", "A\nbar\nC", 'T10', $i++),
                     array("{function name=simple}{\$foo}{/function}{call name='simple'}", "bar", 'T11', $i++),
                     array("{function name=simple}A{\$foo}C{/function}{call name='simple'}", "AbarC", 'T12', $i++),
                     array("{function name=simple}A\n{\$foo}C{/function}{call name='simple'}", "A\nbarC", 'T13', $i++),
                     array("{function name=simple}A{\$foo}\nC{/function}{call name='simple'}", "Abar\nC", 'T14', $i++),
                     array("{function name=simple}A\n{\$foo}\nC{/function}{call name='simple'}", "A\nbar\nC", 'T15', $i++),
        );
            }
