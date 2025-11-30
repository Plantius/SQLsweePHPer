function generate_meta($description=null, $title=null ,$image=null) {
    global $META, $TITLE, $LM_APP_NAME, $lmver;
    
    if (is_null($description)) $description="LMeve: Industry Contribution and Mass Production Tracker."; else $description = htmlentities($description);
    if (is_null($title)) $title = generate_title(); else $title = htmlentities($title);
    if (is_null($image)) $image = getUrl() . "img/lmeve-social.jpg";
    
    $url = parse_url(getUrl());
    $domain = $url['scheme'] . '://' . $url['host'] ;
    $site = $url['host'];
    
    $meta = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="Pragma" CONTENT="content-cache">
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="description" content="' . filter_description($description) . '">
        <meta name="title" content="' . $title . '">
        <meta name="keywords" content="eve-online, eve, ccp, ccp games, lmeve, industry, production, invention, manufacturing, crafting, massively, multiplayer, online, role, playing, game, mmorpg, isk, mmorpg">
        <meta name="robots" content="index,follow">
        <meta property="og:locale" content="en_US">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="' . $site . '">
        <meta property="fb:app_id" content="">
        <meta name="twitter:site" content="@rox_lukas">
        <meta name="twitter:domain" content="' . $domain . '">
        <meta property="application-name" content="LMeve" />
        <meta name="mobile-web-app-capable" content="yes">
        <link rel="apple-touch-icon" sizes="120x120" href="' . getUrl() . 'img/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="' . getUrl() . 'img/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="' . getUrl() . 'img/favicon-16x16.png">
        <link rel="manifest" href="' . getUrl() . 'img/site.webmanifest">
        <link rel="mask-icon" href="' . getUrl() . 'img/safari-pinned-tab.svg" color="#1d2c38">
        <link rel="shortcut icon" href="' . getUrl() . 'img/favicon.ico">
        <meta name="msapplication-TileColor" content="#1d2c38">
        <meta name="msapplication-config" content="' . getUrl() . 'img/browserconfig.xml">
        <meta name="theme-color" content="#1d2c38">
        <meta name="twitter:title" content="' . $title . '">
        <meta name="twitter:image" content="' . $image . '">
        <meta name="twitter:card" content="summary">
        <meta property="og:title" content="' . $title . '">
        <meta property="og:url" content="' . getUrl() . 'index.php?' . $_SERVER['QUERY_STRING'] . '">
        <meta property="twitter:description" content="' . filter_description($description) . '">
        <meta property="og:description" content="' . filter_description($description) . '">
        <meta property="og:image" content="' . $image . '">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>
            ' . $title . '
        </title>';
    $META = $meta;
    return $meta;
}
