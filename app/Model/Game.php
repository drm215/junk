<?php
    class Game extends AppModel {
    
        public $hasMany = array('Week');
        
        public function parser($weekId = null) {
            App::import('Vendor', 'simple_html_dom', array('file'=>'simple_html_dom.php'));
        
            if($weekId == null) {
                $weeks = $this->Week->find('list');
            } else {
                $weeks[1] = $weekId;
            }
            
            foreach($weeks as $week) {
                $this->espnProcessWeek($week);
            }
        }
        
        private function espnProcessWeek($weekId) {
            echo "Processing Week " . $weekId . "\n";
            $url = "http://espn.go.com/college-football/schedule/_/week/" . $weekId;
            echo "URL = " . $url . "\n";
            //$url = "file:///C:/Users/damagee/Desktop/espn.html";
            $this->espnProcessWeekByDays(file_get_html($url), $weekId);
        }
        
        private function espnProcessWeekByDays($html, $weekId) {
            $tables = $html->find('table[class=schedule has-team-logos align-left]');
            echo "Found " . count($tables) . " tables to process.\n";
            foreach($tables as $table) {
                $this->espnProcessWeekGames($table, $weekId, $this->processEspnDate($table->find('caption', 0)->plaintext));
            }
        }
        
        private function espnProcessWeekGames($table, $weekId, $date) {
            $games = $table->find('tr');
            echo "Found " . count($games) . " games to process.\n";
            for($i = 1; $i < count($games); $i++) {
                $this->espnProcessWeekGame($games[$i], $weekId, $date);
            }
        }
        
        private function espnProcessWeekGame($gameTr, $weekId, $date) {
            $tds = $gameTr->find('td');
            $awaySchoolId = $this->getSchoolIdByEspnId($this->getEspnId($tds[0]->find('a', 0)->href, "/college-football/team/_/id/"));
            $homeSchoolId = $this->getSchoolIdByEspnId($this->getEspnId($tds[1]->find('a', 0)->href, "/college-football/team/_/id/"));
            
            $existingGame = $this->find('first', array('recursive' => -1, 'conditions' => array('away_school_id' => $awaySchoolId, 'home_school_id' => $homeSchoolId, 'week_id' => $weekId)));
            if(empty($existingGame)) {
                $game = $this->create();
                $game['Game']['away_school_id'] = $awaySchoolId;
                $game['Game']['home_school_id'] = $homeSchoolId;
                $game['Game']['week_id'] = $weekId;
            } else {
                $game = $existingGame;
            }
            $game['Game']['espn_id'] = $this->getEspnId($tds[2]->find('a', 0)->href, "/college-football/game?gameId=");
            $game['Game']['time'] =  $this->getGameDate($tds[2], $date);
            
            $this->espnSaveGame($game);
        }
        
        private function getSchoolIdByEspnId($espnId) {
            $school = $this->School->find('first', array('recursive' => -1, 'fields' => array('id'), 'conditions' => array('espn_id' => $espnId)));
            return $school['School']['id'];
        }
        
        private function espnSaveGame($game) {
            if($this->save($game)) {
                echo "Game saved successfully.\n";
            } else {
                echo "Game not saved successfully.\n";
                pr($game);
            }
        }
        
        private function getEspnId($espnLink, $prefix) {
            pr($espnLink);
            $sPos = strlen($prefix);
            return substr($espnLink, $sPos, strlen($espnLink) - $sPos);
        }
        
        private function getGameDate($td, $date) {
            $tPos = strpos($td, "T");
            $temp = substr($td, $tPos + 1, strpos($td, "Z") - $tPos - 1) .":00";
            $timeArray = explode(":", $temp);
            return $date . " " . $this->processEspnHour($timeArray[0]) . ":" . $timeArray[1] . ":" . $timeArray[2];
        }
        
        private function processEspnHour($hour) {
            if($hour >= 0 && $hour <=11) {
                $hour = $hour + 19;
            } else {
                $hour = $hour - 5;
            }
            return $hour;
        }
        
        private function processEspnDate($wordyDate) {
            $year = "2015";
            $dateArray = explode(" ", $wordyDate);
            $month = $this->switchMonth($dateArray[1]);
            $day = str_pad($dateArray[2], 2, "0", STR_PAD_LEFT);
            return $year.'-'.$month.'-'.$day;
        }
        
        private function switchMonth($text) {
            $date = date_parse($text);
            return str_pad($date['month'], 2, "0", STR_PAD_LEFT);
        }
        
        public function getGamesByWeek($weekId) {
            $values = array();
            $games = $this->find('all', array('recursive' => -1, 'conditions' => array('week_id' => $weekId)));
            foreach($games as $game) {
                $values[$game['Game']['away_school_id']] = $game;
                $values[$game['Game']['home_school_id']] = $game;
            }
            return $values;
        }
    }
?>