function verifyAuthenticationByApiKey( $request, $right ){
    $authenticationValid = false;
    
    //Parameters in request
    $paramUsername = $request->get('username');
    $paramApiKey = $request->get('apiKey');
    
    //Do not set $serverApiKey to NULL (bypass risk)
    $serverApiKey = EONAPI_KEY;
    
    $usersql = getUserByUsername( $paramUsername );
    $user_right = mysqli_result($usersql, 0, $right);
    $user_type = mysqli_result($usersql, 0, "user_type");
    
    //IF LOCAL USER AND ADMIN USER (No limitation)
    if( $user_type != "1" && $user_right == "1"){
        //ID of the authenticated user
        $user_id = mysqli_result($usersql, 0, "user_id");
        $serverApiKey = apiKey( $user_id );    
    }
    
    
    //Only if API keys match
    if($paramApiKey == $serverApiKey){
        $authenticationValid = true;
    }

    
    return $authenticationValid;
}
