function getCharacterPortrait($characterID,$size=64) {
    //echo("getCorporationLogo($corporationID,$size)");
    if (!is_numeric($characterID) || $characterID == 0) {
        if ($size == 32) {
            return getUrl()."img/character_32.png";
        } else if ($size == 64) {
            return getUrl()."img/character_64.png";
        } else {
            return "";
        }
    }
    if (!is_numeric($size) || ($size!=32 && $size!=64 && $size!=128 && $size!=256 && $size!=512)) $size=64;
    $icon="https://images.evetech.net/characters/$characterID/portrait?size=$size";
    return($icon);
}
