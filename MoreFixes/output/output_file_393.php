    public function get_country_coor($country_code) {
      try {
        // get connession
        $conn = new PDO("mysql:host=".$GLOBALS['$dbhost'].
                        ";dbname=".$GLOBALS['$dbname'].
                        "", $GLOBALS['$dbuser'], $GLOBALS['$dbpass']);

        // set the PDO error mode to exception
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // select query
        $sql = "SELECT lat, lng

                FROM country_name_map

                WHERE code = '$country_code'";
        }
        catch(PDOException $e) {
          echo $e->getMessage();
        }

        // prepare statement
        $stmt = $conn->prepare($sql);

        // execute query
        $stmt->execute();

        // create new empty array
        $infos = array();

        // set the resulting array to associative
        for($i=0; $row = $stmt->fetch(); $i++){
          array_push($infos, array( 'lat' => $row['lat'],
                                    'lng' => $row['lng']
                                                        ));
      }


      // close connession
      $conn = null;

      // return info inserted
      header('Content-Type: application/json');
      echo json_encode($infos);
    }
