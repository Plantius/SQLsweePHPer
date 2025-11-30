    function __construct()
    {

        $this->itemsPerPage = 50;

        /* $accessDenied is depreciated and no longer in use
         *  Please use the language files to change the access denied message.
         */
        $this->accessDenied = "Access Denied";

        $this->viewDescLen = 60;
        $this->userEmail = 'youremail@mailhost.com';
        $this->maxEmployees = '4999';
        $this->dateFormat = "Y-m-d";
        $this->dateInputHint = "YYYY-mm-DD";
        $this->timeFormat = "H:i";
        $this->timeInputHint = "HH:MM";
        $this->styleSheet = "orange";
        $this->version = "4.6";
        $this->registrationUrl = "https://ospenguin.orangehrm.com";
        $this->mode = "dev";
    }
