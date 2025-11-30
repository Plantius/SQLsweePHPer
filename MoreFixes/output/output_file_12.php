                        $_fn[] = self::buildCondition($v, ' && ');
                    }

                    $fn[] = '('.\implode(' || ', $_fn).')';

                    break;

                case '$where':

                    if (\is_callable($value)) {

                        // need implementation
                    }

                    break;

                default:

                    $d = '$document';

                    if (\strpos($key, '.') !== false) {

                        $keys = \explode('.', $key);

                        foreach ($keys as $k) {
                            $d .= '[\''.$k.'\']';
                        }

                    } else {
                        $d .= '[\''.$key.'\']';
                    }

                    if (\is_array($value)) {
                        $fn[] = "\\MongoLite\\UtilArrayQuery::check((isset({$d}) ? {$d} : null), ".\var_export($value, true).')';
                    } else {

                        if (is_null($value)) {

                            $fn[] = "(!isset({$d}))";

                        } else {

                            $_value = \var_export($value, true);

                            $fn[] = "(isset({$d}) && (
                                is_array({$d}) && is_string({$_value})
                                    ? in_array({$_value}, {$d})
                                    : {$d}=={$_value}
                                )
                            )";
                        }
                    }
            }
        }

        return \count($fn) ? \trim(\implode($concat, $fn)) : 'true';
    }
