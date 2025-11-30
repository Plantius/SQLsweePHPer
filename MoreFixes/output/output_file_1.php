                $cfgSite->setSetting( 'db', $key, $value);
            }
            $cfgSite->setSetting( 'site', 'secrethash', substr(md5(time() . ":" . mt_rand()),0,10));
            return true;
        } else {
            return $Errors;
        }
    }
