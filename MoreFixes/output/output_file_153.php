function getCorporationLogo($corporationID,$size=64) {
    //echo("getCorporationLogo($corporationID,$size)");
    if (!is_numeric($corporationID)) return "";
    if (!is_numeric($size) || ($size!=32 && $size!=64 && $size!=128 && $size!=256 && $size!=512)) $size=64;
    $icon="https://images.evetech.net/corporations/$corporationID/logo?size=$size";
    return($icon);
}
