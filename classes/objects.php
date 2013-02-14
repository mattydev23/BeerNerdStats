<?php

class BrewUIItem {
		public $sessionId;
		public $brewery;
		public $name;
		public $generalStyle;
		public $ibu;
		public $abv;
		public $description;
		public $remaining;
		public $sessionStart;
		public $sessionLastCheckIn;
		public $sessionTimeSpan;
		
		public $sessionDiffHour;	
		public $sessionDiffHour2;
		public $sessionDiffHour4;
		public $sessionDiffHour6;
		public $sessionDiffHour8;
		public	$sessionDiffDay;
		
		public $sessionCheckCount;
		public $sessionRemainingDiff;
		
		
		function __construct($sessionId, $brewery, $name, $generalStyle, $ibu, $abv, $description, $remaining, $sessionStart, $sessionLastCheckIn, $sessionDiffHour, $sessionDiffHour2, $sessionDiffHour4, $sessionDiffHour6, $sessionDiffHour8, $sessionDiffDay, $sessionCheckCount, $sessionRemainingDiff) {
			$this->sessionId = $sessionId;
			$this->brewery = $brewery;
			$this->name = $name;
			$this->generalStyle = $generalStyle;
			$this->ibu = $ibu;
			$this->abv = $abv;
			$this->description = $description;
			$this->remaining = number_format($remaining, 2, '.', '');
			$this->sessionRemainingDiff = $sessionRemainingDiff;
			$this->sessionCheckCount = $sessionCheckCount;
					
			$timezone = new DateTimeZone('America/Vancouver');
			$this->sessionLastCheckIn = date_create($sessionLastCheckIn);
			$this->sessionLastCheckIn->setTimezone($timezone);
			$this->sessionStart = date_create($sessionStart);
			$this->sessionStart->setTimezone($timezone);

			$this->sessionTimeSpan = abs(strtotime($sessionStart) - strtotime($sessionLastCheckIn));

			$this->sessionDiffHour = number_format($sessionDiffHour, 2, '.', '');
			$this->sessionDiffHour2 = number_format($sessionDiffHour2, 2, '.', '');
			$this->sessionDiffHour4 = number_format($sessionDiffHour4, 2, '.', '');
			$this->sessionDiffHour6 = number_format($sessionDiffHour6, 2, '.', '');
			$this->sessionDiffHour8 = number_format($sessionDiffHour8, 2, '.', '');
			$this->sessionDiffDay = number_format($sessionDiffDay, 2, '.', '');
		}
}

?>