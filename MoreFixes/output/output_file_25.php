            $nameValuePair = \explode('=', $pair, 2);

            if (\count($nameValuePair) === 2) {
                $key = \urldecode($nameValuePair[0]);
                $value = \urldecode($nameValuePair[1]);
                $result[$key] = $value;
            }
        }

        return $result;
    }
