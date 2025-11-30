                foreach ($pkeys as $k) {
                    $where[] = $k . ' = "' . $row->$k . '"';
                }
