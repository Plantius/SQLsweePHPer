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
