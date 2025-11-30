    public static function svgImageFunction($path, $classes = null, $strip_style = false)
    {
        $path = Utils::fullPath($path);

        $classes = $classes ?: '';

        if (file_exists($path) && !is_dir($path)) {
            $svg = file_get_contents($path);
            $classes = " inline-block $classes";
            $matched = false;

            //Remove xml tag if it exists
            $svg = preg_replace('/^<\?xml.*\?>/','', $svg);

            //Strip style if needed
            if ($strip_style) {
                $svg = preg_replace('/<style.*<\/style>/s', '', $svg);
            }

            //Look for existing class
            $svg = preg_replace_callback('/^<svg[^>]*(class=\"([^"]*)\")[^>]*>/', function($matches) use ($classes, &$matched) {
                if (isset($matches[2])) {
                    $new_classes = $matches[2] . $classes;
                    $matched = true;
                    return str_replace($matches[1], "class=\"$new_classes\"", $matches[0]);
                }
                return $matches[0];
            }, $svg
            );

            // no matches found just add the class
            if (!$matched) {
                $classes = trim($classes);
                $svg = str_replace('<svg ', "<svg class=\"$classes\" ", $svg);
            }

            return trim($svg);
        }

        return null;
    }


    /**
     * Dump/Encode data into YAML format
     *
     * @param array|object $data
     * @param int $inline integer number of levels of inline syntax
     * @return string
     */
    public function yamlEncodeFilter($data, $inline = 10)
    {
        if (!is_array($data)) {
            if ($data instanceof JsonSerializable) {
                $data = $data->jsonSerialize();
            } elseif (method_exists($data, 'toArray')) {
                $data = $data->toArray();
            } else {
                $data = json_decode(json_encode($data), true);
            }
        }

        return Yaml::dump($data, $inline);
    }

    /**
     * Decode/Parse data from YAML format
     *
     * @param string $data
     * @return array
     */
    public function yamlDecodeFilter($data)
    {
        return Yaml::parse($data);
    }

    /**
     * Function/Filter to return the type of variable
     *
     * @param mixed $var
     * @return string
     */
    public function getTypeFunc($var)
    {
        return gettype($var);
    }

    /**
     * Function/Filter to test type of variable
     *
     * @param mixed $var
     * @param string|null $typeTest
     * @param string|null $className
     * @return bool
     */
    public function ofTypeFunc($var, $typeTest = null, $className = null)
    {

        switch ($typeTest) {
            default:
                return false;

            case 'array':
                return is_array($var);

            case 'bool':
                return is_bool($var);

            case 'class':
                return is_object($var) === true && get_class($var) === $className;

            case 'float':
                return is_float($var);

            case 'int':
                return is_int($var);

            case 'numeric':
                return is_numeric($var);

            case 'object':
                return is_object($var);

            case 'scalar':
                return is_scalar($var);

            case 'string':
                return is_string($var);
        }
    }

    /**
     * @param Environment $env
     * @param array $array
     * @param callable|string $arrow
     * @return array|CallbackFilterIterator
     * @throws RuntimeError
     */
    function filterFilter(Environment $env, $array, $arrow)
    {
        if (!$arrow instanceof \Closure && !is_string($arrow) || Utils::isDangerousFunction($arrow)) {
            throw new RuntimeError('Twig |filter("' . $arrow . '") is not allowed.');
        }

        return twig_array_filter($env, $array, $arrow);
    }

    /**
     * @param Environment $env
     * @param array $array
     * @param callable|string $arrow
     * @return array|CallbackFilterIterator
     * @throws RuntimeError
     */
    function mapFilter(Environment $env, $array, $arrow)
    {
        if (!$arrow instanceof \Closure && !is_string($arrow) || Utils::isDangerousFunction($arrow)) {
            throw new RuntimeError('Twig |map("' . $arrow . '") is not allowed.');
        }

        return twig_array_map($env, $array, $arrow);
    }
}

