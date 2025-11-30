            echo '<div class="error"><h2>'. ucfirst($type) .' ' .  translate('ML_ISSUES') . '</h2> </div>';
            echo '<div id="details' . $type . '" >';
            foreach ($issues as $file=>$issue) {
                $file = str_replace($this->pathToModule . '/', '', $file);
                echo '<div style="position:relative;left:10px"><b>' . $file . '</b></div><div style="position:relative;left:20px">';
                if (is_array($issue)) {
                    foreach ($issue as $i) {
                        echo "$i<br>";
                    }
                } else {
                    echo "$issue<br>";
                }
                echo "</div>";
            }
            echo '</div>';
        }
        echo "<br><input class='button' onclick='document.location.href=\"index.php?module=Administration&action=UpgradeWizard&view=module\"' type='button' value=\"" . translate('LBL_UW_BTN_BACK_TO_MOD_LOADER') . "\" />";
    }
