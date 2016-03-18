<?php
    class Player extends AppModel {

        public $belongsTo = array('School');
        public $hasMany = array('Playerentry');

        public function getPlayers($position, $userId, $weekId, $playoffFlag) {
            $this->Game = ClassRegistry::init('Game');
            $this->Userentry = ClassRegistry::init('Userentry');
            $this->School = ClassRegistry::init('School');

            $players = $this->getAvailablePlayers();
            $schools = $this->School->find('list', array('recursive' => -1));
            $schedule = $this->Game->getGamesByWeek($weekId);
            $userentries = $this->Userentry->calculatePreviousUserEntries($weekId, $playoffFlag, $userId);
            $playerData = $this->printPlayersData($players, $userentries, $schedule, $schools);
            return $playerData;
        }
      
      public function getAvailablePlayers() {
            $players = array();
            $players['QB'] = $this->getAvailablePlayersByPosition(array('QB'));
            $players['RB'] = $this->getAvailablePlayersByPosition(array('RB'));
            $players['WR'] = $this->getAvailablePlayersByPosition(array('WR'));
            $players['F'] = $this->getAvailablePlayersByPosition(array('RB','WR','TE'));
            $players['K'] = $this->getAvailablePlayersByPosition(array('K'));
            $players['D'] = $this->getAvailablePlayersByPosition(array('D'));

            return $players;
        }
      
      /**
        Gets the list of players available by the given position.
        Restricts based on previous picks and schools who have already played in that week
        **/
        private function getAvailablePlayersByPosition($position) {
            $this->Playerentry->unbindModel(array('belongsTo' => array('Week')));
            $this->unbindModel(array('hasMany' => array('Playerentry')));
            $tempPlayerEntries = $this->Playerentry->find('all',
                array(
                    'conditions' => array('position' => $position, 'Player.year' => Configure::write('current.year')),
                    'fields' => array('SUM(points)',
                        'SUM(pass_yards)','SUM(pass_tds)',
                        'SUM(rush_yards)','SUM(rush_tds)',
                        'SUM(receive_yards)','SUM(receive_tds)',
                        'SUM(return_yards)','SUM(return_tds)',
                        'SUM(field_goals)','SUM(pat)',
                        'SUM(points_allowed)','SUM(fumble_recovery)',
                        'SUM(def_ints)','SUM(def_tds)','SUM(safety)',
                        'Player.name, Player.school_id, Player.id, Player.position'),
                    'group' => array('Player.id'),
                    'recursive' => 2,
                    'order' => array('SUM(points) DESC')
                )
            );

            $playerEntries = array();
            foreach($tempPlayerEntries as $temp) {
                $playerEntries[$temp['Player']['id']] = $temp;
            }
            return $playerEntries;
        }
        

        public function parser($playerRow, $schoolId) {
            $columns = $playerRow->find('td');

            $espnId = $this->getEspnId($columns[1]->find('a',0)->href);
            $name = $columns[1]->plaintext;
            $position = $columns[2]->plaintext;

            $player = null;
            $players = $this->find('all', array('conditions' => array('name' => $name), 'recursive' => -1));

            if(count($players) == 0) {
                $player = $this->create();
                $player['Player']['name'] = $name;
            } else if(count($players == 1)) {
                $player = $players[0];
            } else {
                echo $name . " returned more than one row.";
            }

            if($player != null) {
                $player['Player']['school_id'] = $schoolId;
                $player['Player']['year'] = Configure::write('current.year');
                $player['Player']['position'] = $position;
                $player['Player']['espn_id'] = $espnId;
            }

            $this->savePlayer($player);
        }

        private function savePlayer($player) {
            if($this->save($player)) {
                echo $player['Player']['name']." saved successfully.\n";
            } else {
                echo $player['Player']['name']." not saved successfully.\n";
            }
        }

        public function getEspnId($espnLink) {
            $bPos = strlen("http://espn.go.com/college-football/player/_/id/");
            $ePos = strpos($espnLink, "/", $bPos);
            $espnId = substr($espnLink, $bPos, $ePos - $bPos);
            return $espnId;
        }

    public function printPlayersData($players, $userentries, $schedule, $schools) {
        $html = "";
        $html .= $this->printPlayerData('QB', '', $players, $userentries, $schedule, $schools);
/*        $html .= $this->printPlayerData('RB', '1', $players, $userentries, $schedule, $schools);
        $html .= $this->printPlayerData('RB', '2', $players, $userentries, $schedule, $schools);
        $html .= $this->printPlayerData('WR', '1', $players, $userentries, $schedule, $schools);
        $html .= $this->printPlayerData('WR', '2', $players, $userentries, $schedule, $schools);
        $html .= $this->printPlayerData('F', '', $players, $userentries, $schedule, $schools);
        $html .= $this->printPlayerData('K', '', $players, $userentries, $schedule, $schools);
        $html .= $this->printPlayerData('D', '', $players, $userentries, $schedule, $schools);*/
        return $html;
    }

    private function printPlayerData($position, $secondaryPosition, $players, $userentries, $schedule, $schools) {
        $html = "<div id='".$position.$secondaryPosition."' style=\"overflow-y: scroll; height:400px;\">";
        $html .= "<table id='player-data-".$position.$secondaryPosition."'>";
        $html .= "<thead>";
        $html .= "<tr>";
        $html .= "<th colspan=\"4\"/>";
        if($position == "QB" || $position == "RB" || $position == "WR" || $position == "F") {
            $html .= "<th colspan=\"2\">Passing</th>";
            $html .= "<th colspan=\"2\">Rushing</th>";
            $html .= "<th colspan=\"2\">Receiving</th>";
            $html .= "<th colspan=\"2\">Return</th>";
            $html .= "</tr>";
            $html .= "<tr>";
            $html .= "<th/>";
            $html .= "<th>Player</th>";
            $html .= "<th>Opponent</th>";
            $html .= "<th>Points</th>";
            $html .= "<th>Yards</th>";
            $html .= "<th>TDs</th>";
            $html .= "<th>Yards</th>";
            $html .= "<th>TDs</th>";
            $html .= "<th>Yards</th>";
            $html .= "<th>TDs</th>";
            $html .= "<th>Yards</th>";
            $html .= "<th>TDs</th>";
        } else if($position == "K") {
            $html .= "<th colspan=\"2\">Kicking</th>";
            $html .= "</tr>";
            $html .= "<tr>";
            $html .= "<th/>";
            $html .= "<th>Player</th>";
            $html .= "<th>Opponent</th>";
            $html .= "<th>Points</th>";
            $html .= "<th>FGs</th>";
            $html .= "<th>PATs</th>";
        } else if($position == "D") {
            $html .= "<th colspan=\"5\">Defense</th>";
            $html .= "</tr>";
            $html .= "<tr>";
            $html .= "<th/>";
            $html .= "<th>Player</th>";
            $html .= "<th>Opponent</th>";
            $html .= "<th>Points</th>";
            $html .= "<th>PA</th>";
            $html .= "<th>Fumbles</th>";
            $html .= "<th>INTs</th>";
            $html .= "<th>TDs</th>";
            $html .= "<th>Safeties</th>";
        }
        $html .= "</tr>";
        $html .= "</thead>";
        $html .= "<tbody>";

        foreach($players[$position] as $row) {
            $alreadyPlayed = false;
            $buttonLabel = "Select";
            $buttonStyle = "";
            $opponent = "";
            $opponentId = "";
            if(isset($schedule[$row['Player']['School']['id']])) {
                $lockedTime = strtotime($schedule[$row['Player']['School']['id']]['Game']['time']) - 10 * 60;
                if(time() > $lockedTime) {
                    $buttonLabel = "Locked";
                    $buttonStyle = " disabled='disabled'";
                }
                $awaySchoolId = $schedule[$row['Player']['School']['id']]['Game']['away_school_id'];
                $homeSchoolId = $schedule[$row['Player']['School']['id']]['Game']['home_school_id'];
                if($row['Player']['School']['id'] == $awaySchoolId) {
                    $opponentId = $homeSchoolId;
                } else {
                    $opponentId = $awaySchoolId;
                }

                $opponent = $schools[$opponentId];
            }

            $class = "";
            if(!isset($userentries[$position][$row['Player']['id']])) {
                $alreadyPlayed = true;
                $class = "strike";
            }

            if($opponentId == "") {
                $buttonStyle = " disabled='disabled'";
                $buttonLabel = "Inactive";
            }

            $playerId = $position.$secondaryPosition.'_'.$row['Player']['id'];
            $html .= "<tr>";
            $html .= "<td>";
            if(!$alreadyPlayed) {
                $html .= "<button id='".$playerId."' class='".$class."' ".$buttonStyle." type='button'>".$buttonLabel."</button>";
            }
            $html .= "</td>";
            $html .= "<td class=".$class.">";
            $html .= "<div id='id_".$playerId."'>";
            $html .= $row['Player']['name']. ", ". $row['Player']['position']. ", ". $row['Player']['School']['name'];
            $html .= "</div>";
            $html .= "</td>";
            $html .= "<td>";
            $html .= $opponent;
            $html .= "</td>";
            $html .= "<td>";
            $html .= round($row['0']['SUM(points)'],0);
            $html .= "</td>";
            if($position == "QB" || $position == "RB" || $position == "WR" || $position == "F") {
                $html .= "<td>";
                $html .= $row['0']['SUM(pass_yards)'];
                $html .= "</td>";
                $html .= "<td>";
                $html .= $row['0']['SUM(pass_tds)'];
                $html .= "</td>";
                $html .= "<td>";
                $html .= $row['0']['SUM(rush_yards)'];
                $html .= "</td>";
                $html .= "<td>";
                $html .= $row['0']['SUM(rush_tds)'];
                $html .= "</td>";
                $html .= "<td>";
                $html .= $row['0']['SUM(receive_yards)'];
                $html .= "</td>";
                $html .= "<td>";
                $html .= $row['0']['SUM(return_yards)'];
                $html .= "</td>";
                $html .= "<td>";
                $html .= $row['0']['SUM(return_tds)'];
                $html .= "</td>";
                $html .= "<td>";
                $html .= $row['0']['SUM(return_yards)'];
                $html .= "</td>";
            } else if($position == "K") {
                $html .= "<td>";
                $html .= $row['0']['SUM(field_goals)'];
                $html .= "</td>";
                $html .= "<td>";
                $html .= $row['0']['SUM(pat)'];
                $html .= "</td>";
            } else if($position == "D") {
                $html .= "<td>";
                $html .= $row['0']['SUM(points_allowed)'];
                $html .= "</td>";
                $html .= "<td>";
                $html .= $row['0']['SUM(fumble_recovery)'];
                $html .= "</td>";
                $html .= "<td>";
                $html .= $row['0']['SUM(def_ints)'];
                $html .= "</td>";
                $html .= "<td>";
                $html .= $row['0']['SUM(def_tds)'];
                $html .= "</td>";
                $html .= "<td>";
                $html .= $row['0']['SUM(safety)'];
                $html .= "</td>";
            }

            $html .= "</tr>";
        }
        $html .= "</tbody>";
        $html .= "</table>";
        $html .="</div>";
        return $html;
    }
}
?>