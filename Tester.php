<?php
namespace Stanford\Tester;
/** @var \Stanford\Tester\Tester $module **/

use DateTime;
use DateTimeZone;
use \REDCap;
use Exception;
use ExternalModules\ExternalModules;
use Stanford\Tester\emLoggerTrait;

require_once "emLoggerTrait.php";

class Tester extends \ExternalModules\AbstractExternalModule {

    use emLoggerTrait;

    public function __construct() {
		parent::__construct();
	}

    /******************************************************************************************************************/
    /* CRON METHODS                                                                                                   */
    /******************************************************************************************************************/
    /**
     * This function will be called by the Cron on a daily basis.
     */
    public function checkLatestDate() {
        // Set PID
        $this->pid                      = $this->getSystemSetting('r2s-pid');

        // Set Event ID
        $this->eventId                  = $this->getSystemSetting('r2s-event-id');

        // Email parameters
        $this->emailTo                  = $this->getSystemSetting('r2s-email-to');
        $this->emailFrom                = $this->getSystemSetting('r2s-email-from');
        $this->emailSubject             = $this->getSystemSetting('r2s-email-subject');
        $this->emailBody                = $this->getSystemSetting('r2s-email-body');

        // Capture input dates
        $data = REDCap::getData($this->pid, 'array', null, array('date_stamp'), $this->eventId);

        // Capture latest record
        $latestRecord = end($data);

        // Capture latest date of latest record
        $date = array_values($latestRecord);
        $latestDate = $date[0]['date_stamp'];

        // Convert latest date to epoch time
        $latestTime = strtotime($latestDate);

        // Capture current epoch time
        $currentTime =  strtotime('now');

        // Capture readable date & time of latest date record and current date & time
        $latestResult = date('Y-m-d H:i:s', $latestTime);
        $currentResult = date('Y-m-d H:i:s', $currentTime);
        $this->emDebug('Latest input date & time: ' . $latestResult , 'Current date & time: ' . $currentResult);

        // Difference between latestTime and currentTime
        $diff = $currentTime - $latestTime;

        // Send email if EM did not update
        if ($diff > 86400) {
            $status = REDCap::email($this->emailTo, $this->emailFrom, $this->emailSubject, $this->emailBody . 'Latest input date & time: ' . $latestResult . '<br>' . 'Current date & time: ' . $currentResult . '<br>' . 'The time difference between "Latest" and "Current" date & time is more than 24 hours');
            return $status;
        }
    }
}