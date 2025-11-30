function getAllianceLogo($allianceID,$size=64) {
    //echo("getCorporationLogo($corporationID,$size)");
    if (!is_numeric($allianceID)) return "";
    if (!is_numeric($size) || ($size!=32 && $size!=64 && $size!=128 && $size!=256 && $size!=512)) $size=64;
    $icon="https://images.evetech.net/alliances/$allianceID/logo?size=$size";
    return($icon);
}
