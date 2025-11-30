    public function shouldRun(DateTime $date)
    {
        global $timedate;

        $runDate = clone $date;
        $this->handleTimeZone($runDate);

        $cron = Cron\CronExpression::factory($this->schedule);
        if (empty($this->last_run) && $cron->isDue($runDate)) {
            return true;
        }

        $lastRun = $this->last_run ? $timedate->fromDb($this->last_run) : $timedate->fromDb($this->date_entered);
        
        $this->handleTimeZone($lastRun);
        $next = $cron->getNextRunDate($lastRun);

        return $next <= $runDate;
    }
