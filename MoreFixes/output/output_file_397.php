    public function get_info($interval) {
      try {
        // get connession
        $conn = new PDO("mysql:host=".$GLOBALS['$dbhost'].
                        ";dbname=".$GLOBALS['$dbname'].
                        "", $GLOBALS['$dbuser'], $GLOBALS['$dbpass']);

        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // select query
        $sql = "SELECT    country_code,
                          GROUP_CONCAT(CONCAT( release_tag,'#',num )) AS installations,
                          country_name

                FROM      ( SELECT  country_code,
                                    release_tag,
                                    country_name,
                                    reg_date,
                                    COUNT(release_tag) AS num
                            FROM phone_home_tb ";

        if ($interval!=='1') {
          $sql .= " WHERE reg_date >= DATE_SUB(CURDATE(), INTERVAL $interval DAY)";
        }

        $sql .= " GROUP BY release_tag, country_code
        ) AS t
        GROUP BY  country_code;";

        // prepare statement
        $stmt = $conn->prepare($sql);

        // execute query
        $stmt->execute();

        // create new empty array
        $infos = array();

        // set the resulting array to associative
        for($i=0; $row = $stmt->fetch(); $i++){
          array_push($infos, array( 'installations'         => $row['installations'],
                                    'country_code'          => $row['country_code'],
                                    'country_name'          => $row['country_name']
                                  ));
        }

        // close connession
        $conn = null;

        // return info inserted
        header('Content-Type: application/json');
        echo json_encode($infos);

      }
      catch(PDOException $e) {
        echo $e->getMessage();
      }

    }
