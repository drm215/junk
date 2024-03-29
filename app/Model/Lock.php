<?php
	class Lock extends AppModel {
	
		/**
		Gets the schools who have an early game in the given week
		**/
		public function getSchoolLocks($userEntry, $weekId) {
			$schools = array($userEntry['QB']['school'],$userEntry['RB1']['school'],$userEntry['RB2']['school'],$userEntry['WR1']['school'],$userEntry['WR2']['school'],$userEntry['FLEX']['school'],$userEntry['K']['school'],$userEntry['D']['school']);
			$locks = $this->find('list', array('fields' => array('school', 'time'), 'conditions' => array('school' => $schools, 'week_id' => $weekId)));
			return $locks;
		}
	}
?>