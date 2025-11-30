function getTypeIDicon($typeID, $size=32, $type=null) {
    if (!is_numeric($typeID)) $typeID=0;
    if (!is_numeric($size) || ($size!=32 && $size!=64 && $size!=128 && $size!=256 && $size!=512)) $size=32;
    
    $bp = getBlueprint($typeID);
    
    if ($bp === FALSE) {
        if (is_null($type) || ($type != 'icon' && $type != 'render')) {
             $type = 'icon';
        }
        if ($size >= 512) {
            $type = 'render';
        }
    } else {
        if (is_null($type) || ($type != 'bp' && $type != 'bpc')) {
            if ($bp['techLevel'] == 1) {
                $type = 'bp';
            } else {
                $type = 'bpc';
            }
        }
        
    }
    
    if ($size != 512) {
        if (file_exists("../wwwroot/ccp_img/${typeID}_${size}.png")) {
            $icon=getUrl()."ccp_img/${typeID}_${size}.png";
        } else {
            //$icon="https://imageserver.eveonline.com/Type/${typeID}_${size}.png";
            $icon="https://images.evetech.net/types/${typeID}/$type?size=${size}";
        }
    } else {
        if (file_exists("../wwwroot/ccp_renders/${typeID}.png")) {
            $icon=getUrl()."ccp_renders/${typeID}.png";
        } else {
            //$icon="https://imageserver.eveonline.com/Render/${typeID}_${size}.png";
            $icon="https://images.evetech.net/types/${typeID}/$type?size=${size}";
        }
    }
    return($icon);
}
