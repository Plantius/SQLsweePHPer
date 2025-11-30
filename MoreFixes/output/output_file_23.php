            $jsFileInclude .= htmlentities($_POST['js_keys'][$i]) . ':' . '"' . htmlentities($_POST['js_values'][$i]) . '",' . "\n";
            $prevFile = $jsFile;
            $i++;
        }
        $jsFileInclude .= "};";
        savefile($jsFile, $jsFileInclude);
    }
