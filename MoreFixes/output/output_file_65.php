				} elseif (isset($memInfo['TotalVisibleMemorySize'])) {
					$totalMem = $memInfo['TotalVisibleMemorySize'];
				} else {
					break;
				}

				if ($variables[$name] < ($r['value']*$totalMem/100)) {
					if (isset($r['class']) && $r['class'] == 'warning') {
						$class = 'textWarning';
					} else {
						$class = 'textError';
					}
				}

				print "<td>" . $name . "</td>\n";
				print "<td class='$class'>" . round($variables[$name]/1024/1024,0) . "M</td>\n";
				print "<td>>=" . round($r['value']*$totalMem/100/1024/1024,0) . "M</td>\n";
				print "<td class='$class'>" . $r['comment'] . "</td>\n";

				break;
			}
