    public function validate() {
        header("Content-Type: application/json");
        $id = $this->input->post('id', TRUE);
        $type = $this->input->post('type', TRUE);
        //The above parameters could cause an SQL injection vulnerability due to the non standard
        //SQL query in leave_model::detectOverlappingLeaves
        $date = $this->input->post('startdate', TRUE);
        $d = DateTime::createFromFormat('Y-m-d', $date);
        $startdate = ($d && $d->format('Y-m-d') === $date)?$date:'1970-01-01';
        $date = $this->input->post('enddate', TRUE);
        $d = DateTime::createFromFormat('Y-m-d', $date);
        $enddate = ($d && $d->format('Y-m-d') === $date)?$date:'1970-01-01';
        $startdatetype = $this->input->post('startdatetype', TRUE);     //Mandatory field checked by frontend
        $enddatetype = $this->input->post('enddatetype', TRUE);       //Mandatory field checked by frontend
        $leave_id = $this->input->post('leave_id', TRUE);
        $leaveValidator = new stdClass;
        $deductDayOff = FALSE;
        if (isset($id) && isset($type)) {
            $typeObject = $this->types_model->getTypeByName($type);
            $deductDayOff = $typeObject['deduct_days_off'];
            if (isset($startdate) && $startdate !== "") {
                $leaveValidator->credit = $this->leaves_model->getLeavesTypeBalanceForEmployee($id, $type, $startdate);
            } else {
                $leaveValidator->credit = $this->leaves_model->getLeavesTypeBalanceForEmployee($id, $type);
            }
        }
        if (isset($id) && isset($startdate) && isset($enddate)) {
            if (isset($leave_id)) {
                $leaveValidator->overlap = $this->leaves_model->detectOverlappingLeaves($id, $startdate, $enddate, $startdatetype, $enddatetype, $leave_id);
            } else {
                $leaveValidator->overlap = $this->leaves_model->detectOverlappingLeaves($id, $startdate, $enddate, $startdatetype, $enddatetype);
            }
        }

        //Returns end date of the yearly leave period or NULL if the user is not linked to a contract
        $this->load->model('contracts_model');
        $startentdate = NULL;
        $endentdate = NULL;
        $hasContract = $this->contracts_model->getBoundaries($id, $startentdate, $endentdate);
        $leaveValidator->PeriodStartDate = $startentdate;
        $leaveValidator->PeriodEndDate = $endentdate;
        $leaveValidator->hasContract = $hasContract;

        //Add non working days between the two dates (including their type: morning, afternoon and all day)
        if (isset($id) && ($startdate!='') && ($enddate!='')  && $hasContract===TRUE) {
            $this->load->model('dayoffs_model');
            $leaveValidator->listDaysOff = $this->dayoffs_model->listOfDaysOffBetweenDates($id, $startdate, $enddate);
            //Sum non-working days and overlapping with day off detection
            $result = $this->leaves_model->actualLengthAndDaysOff($id, $startdate, $enddate, $startdatetype, $enddatetype, $leaveValidator->listDaysOff, $deductDayOff);
            $leaveValidator->overlapDayOff = $result['overlapping'];
            $leaveValidator->lengthDaysOff = $result['daysoff'];
            $leaveValidator->length = $result['length'];
        }
        //If the user has no contract, simply compute a date difference between start and end dates
        if (isset($id) && isset($startdate) && isset($enddate)  && $hasContract===FALSE) {
            $leaveValidator->length = $this->leaves_model->length($id, $startdate, $enddate, $startdatetype, $enddatetype);
        }

        //Repeat start and end dates of the leave request
        $leaveValidator->RequestStartDate = $startdate;
        $leaveValidator->RequestEndDate = $enddate;

        echo json_encode($leaveValidator);
    }
