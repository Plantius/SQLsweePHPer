    public function scanFile($file)
    {
        $issues = array();
        if (!$this->isValidExtension($file)) {
            $issues[] = translate('ML_INVALID_EXT');
            $this->issues['file'][$file] = $issues;
            return $issues;
        }
        if ($this->isConfigFile($file)) {
            $issues[] = translate('ML_OVERRIDE_CORE_FILES');
            $this->issues['file'][$file] = $issues;
            return $issues;
        }
        $contents = file_get_contents($file);
        if (!$this->isPHPFile($contents)) {
            return $issues;
        }
        $tokens = @token_get_all($contents);
        $checkFunction = false;
        $possibleIssue = '';
        $lastToken = false;
        foreach ($tokens as $index=>$token) {
            if (is_string($token[0])) {
                switch ($token[0]) {
                    case '`':
                        $issues['backtick'] = translate('ML_INVALID_FUNCTION') . " '`'";
                        // no break
                    case '(':
                        if ($checkFunction) {
                            $issues[] = $possibleIssue;
                        }
                        break;
                }
                $checkFunction = false;
                $possibleIssue = '';
            } else {
                $token['_msi'] = token_name($token[0]);
                switch ($token[0]) {
                    case T_WHITESPACE: break;
                    case T_EVAL:
                        if (in_array('eval', $this->blackList) && !in_array('eval', $this->blackListExempt)) {
                            $issues[]= translate('ML_INVALID_FUNCTION') . ' eval()';
                        }
                        break;
                    case T_STRING:
                        $token[1] = strtolower($token[1]);
                        if ($lastToken !== false && $lastToken[0] == T_NEW) {
                            if (!in_array($token[1], $this->classBlackList)) {
                                break;
                            }
                            if (in_array($token[1], $this->classBlackListExempt)) {
                                break;
                            }
                        } elseif ($token[0] == T_DOUBLE_COLON) {
                            if (!in_array($lastToken[1], $this->classBlackList)) {
                                break;
                            }
                            if (in_array($lastToken[1], $this->classBlackListExempt)) {
                                break;
                            }
                        } else {
                            //if nothing else fit, lets check the last token to see if this is a possible method call
                            if ($lastToken !== false &&
                            ($lastToken[0] == T_OBJECT_OPERATOR ||  $lastToken[0] == T_DOUBLE_COLON)) {
                                // check static blacklist for methods
                                if (!empty($this->methodsBlackList[$token[1]])) {
                                    if ($this->methodsBlackList[$token[1]] == '*') {
                                        $issues[]= translate('ML_INVALID_METHOD') . ' ' .$token[1].  '()';
                                        break;
                                    } else {
                                        if ($lastToken[0] == T_DOUBLE_COLON && $index > 2 && $tokens[$index-2][0] == T_STRING) {
                                            $classname = strtolower($tokens[$index-2][1]);
                                            if (in_array($classname, $this->methodsBlackList[$token[1]])) {
                                                $issues[]= translate('ML_INVALID_METHOD') . ' ' .$classname . '::' . $token[1]. '()';
                                                break;
                                            }
                                        }
                                    }
                                }
                                //this is a method call, check the black list
                                if (in_array($token[1], $this->methodsBlackList)) {
                                    $issues[]= translate('ML_INVALID_METHOD') . ' ' .$token[1].  '()';
                                }
                                break;
                            }


                            if (!in_array($token[1], $this->blackList)) {
                                break;
                            }
                            if (in_array($token[1], $this->blackListExempt)) {
                                break;
                            }
                        }
                        // no break
                    case T_VARIABLE:
                        $checkFunction = true;
                        $possibleIssue = translate('ML_INVALID_FUNCTION') . ' ' .  $token[1] . '()';
                        break;

                    default:
                        $checkFunction = false;
                        $possibleIssue = '';

                }
                if ($token[0] != T_WHITESPACE) {
                    $lastToken = $token;
                }
            }
        }
        if (!empty($issues)) {
            $this->issues['file'][$file] = $issues;
        }

        return $issues;
    }
