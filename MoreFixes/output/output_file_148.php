function getApiKey(){
    $request = \Slim\Slim::getInstance()->request();
    $response = \Slim\Slim::getInstance()->response();
    
    $authenticationValid = verifyAuthenticationByPassword( $request );
    if( $authenticationValid == TRUE ){
        //ID of the authenticated user
        $paramUsername = $request->get('username');
        $usersql = getUserByUsername( $paramUsername );
        $user_id = mysqli_result($usersql, 0, "user_id");
        
        $serverApiKey = apiKey( $user_id );
        
        $array = array("EONAPI_KEY" => $serverApiKey);
        $result = getJsonResponse($response, "200", $array);
        echo $result;
    }
    else{
        $array = array("message" => "The username-password credentials of your authentication can not be accepted or the user is not in a group");
        $result = getJsonResponse($response, "401", $array);
        echo $result;
    }  
}
