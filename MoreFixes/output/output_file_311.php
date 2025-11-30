    private static function evaluate($func, $a, $b) {

        $r = false;

        if (\is_null($a) && $func != '$exists') {
            return false;
        }

        switch ($func) {
            case '$eq' :
                $r = $a == $b;
                break;

            case '$ne' :
                $r = $a != $b;
                break;

            case '$gte' :
                if ( (\is_numeric($a) && \is_numeric($b)) || (\is_string($a) && \is_string($b)) ) {
                    $r = $a >= $b;
                }
                break;

            case '$gt' :
                if ( (\is_numeric($a) && \is_numeric($b)) || (\is_string($a) && \is_string($b)) ) {
                    $r = $a > $b;
                }
                break;

            case '$lte' :
                if ( (\is_numeric($a) && \is_numeric($b)) || (\is_string($a) && \is_string($b)) ) {
                    $r = $a <= $b;
                }
                break;

            case '$lt' :
                if ( (\is_numeric($a) && \is_numeric($b)) || (\is_string($a) && \is_string($b)) ) {
                    $r = $a < $b;
                }
                break;

            case '$in' :
                if (\is_array($a)) {
                    $r = \is_array($b) ? \count(\array_intersect($a, $b)) : false;
                } else {
                    $r = \is_array($b) ? \in_array($a, $b) : false;
                }
                break;

            case '$nin' :
                if (\is_array($a)) {
                    $r = \is_array($b) ? (\count(\array_intersect($a, $b)) === 0) : false;
                } else {
                    $r = \is_array($b) ? (\in_array($a, $b) === false) : false;
                }
                break;

            case '$has' :
                if (\is_array($b))
                    throw new \InvalidArgumentException('Invalid argument for $has array not supported');
                if (!\is_array($a)) $a = @\json_decode($a, true) ?  : [];
                $r = \in_array($b, $a);
                break;

            case '$all' :
                if (!\is_array($a)) $a = @\json_decode($a, true) ?  : [];
                if (!\is_array($b))
                    throw new \InvalidArgumentException('Invalid argument for $all option must be array');
                $r = \count(\array_intersect_key($a, $b)) == \count($b);
                break;

            case '$regex' :
            case '$preg' :
            case '$match' :
            case '$not':
                $r = (boolean) @\preg_match(isset($b[0]) && $b[0]=='/' ? $b : '/'.$b.'/iu', $a, $match);
                if ($func === '$not') {
                    $r = !$r;
                }
                break;

            case '$size' :
                if (!\is_array($a)) $a = @\json_decode($a, true) ?  : [];
                $r = (int) $b == \count($a);
                break;

            case '$mod' :
                if (! \is_array($b))
                    throw new \InvalidArgumentException('Invalid argument for $mod option must be array');
                $r = $a % $b[0] == $b[1] ?? 0;
                break;

            case '$func' :
            case '$fn' :
            case '$f' :
                if (\is_string($b) || !\is_callable($b))
                    throw new \InvalidArgumentException('Function should be callable');
                $r = $b($a);
                break;

            case '$exists':
                $r = $b ? !\is_null($a) : \is_null($a);
                break;

            case '$fuzzy':
            case '$text':

                $distance = 3;
                $minScore = 0.7;

                if (\is_array($b) && isset($b['$search'])) {

                    if (isset($b['$minScore']) && \is_numeric($b['$minScore'])) $minScore = $b['$minScore'];
                    if (isset($b['$distance']) && \is_numeric($b['$distance'])) $distance = $b['$distance'];

                    $b = $b['search'];
                }

                $r = fuzzy_search($b, $a, $distance) >= $minScore;
                break;

            default :
                throw new \ErrorException("Condition not valid ... Use {$func} for custom operations");
                break;
        }

        return $r;
    }
