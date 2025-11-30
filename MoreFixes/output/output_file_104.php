    function __construct() {

        // setup variables
        define( 'CFS_VERSION', '2.6.4' );
        define( 'CFS_DIR', dirname( __FILE__ ) );
        define( 'CFS_URL', plugins_url( '', __FILE__ ) );

        // get the gears turning
        include( CFS_DIR . '/includes/init.php' );
    }
