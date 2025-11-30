    public static function getIconName($module)
    {
        return isset(static::$iconNames[$module])
            ? static::$iconNames[$module]
            : strtolower(str_replace('_', '-', $module));
    }
