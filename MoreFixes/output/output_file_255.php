function verifyAuthenticationByPassword( $request ){
    $authenticationValid = false;
    
    //Parameters in request
    $paramUsername = mysql_real_escape_string($request->get('username'));
    $paramPassword = mysql_real_escape_string($request->get('password'));
    
    $usersql = getUserByUsername( $paramUsername );
    $user_right = mysqli_result($usersql, 0, "readonly");
    $user_type = mysqli_result($usersql, 0, "user_type");
    
    //IF LOCAL USER AND ADMIN USER (No limitation)
    if( $user_type != "1" && $user_right == "1"){
        $userpasswd = mysqli_result($usersql, 0, "user_passwd");
        $password = md5($paramPassword);
        
        //IF match the hashed password
        if($userpasswd == $password)
            $authenticationValid = true;
    }
    
    return $authenticationValid;
}
