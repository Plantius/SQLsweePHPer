    public static function rename($a_source, $a_target)
    {
        $pi = pathinfo($a_target);
        if (!in_array(strtolower($pi["extension"]), self::getValidExtensions())) {
            include_once("./Services/Utilities/classes/class.ilFileUtilsException.php");
            throw new ilFileUtilsException("Invalid target file " . $pi["basename"] . ".");
        }

        return rename($a_source, $a_target);
    }
