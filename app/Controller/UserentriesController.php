<?php
    class UserentriesController extends AppController {

        public function index() {
            $this->Userentry->unbindModel(array('belongsTo' => array('QB', 'RB1', 'RB2', 'WR1', 'WR2', 'F', 'K', 'D', 'User')));
            $records = $this->Userentry->find('all', array('conditions' => array('Userentry.user_id' => $this->Auth->user('id')), 'recursive' => 0));
            $data = array();
            $this->Playerentry = ClassRegistry::init('Playerentry');
            foreach ($records as $record) {
                $weekId = $record['Week']['id'];
                $UserId = $this->Auth->user('id');

                $playersArray = array($record['Userentry']['qb_id'],$record['Userentry']['rb1_id'],$record['Userentry']['rb2_id'],$record['Userentry']['wr1_id'],$record['Userentry']['wr2_id'],$record['Userentry']['f_id'],$record['Userentry']['k_id'],$record['Userentry']['d_id']);
                $record['Playerentry'] = $this->Playerentry->getTotalPointsByWeek($weekId, $playersArray);

                array_push($data, $record);
            }
            $this->set('records', $data);

            $this->Week = ClassRegistry::init('Week');
            $this->set('weeks', $this->Week->find('all', array('fields' => array('id', 'name'), 'recursive' => 0)));
        }

        private function saveUserentry($userentry, $data) {
            if ($this->request->is(array('post', 'put'))) {

                $schoolLocked = false;
                if(isset($userentry['QB']['school_locked'])) {
                    $schoolLocked = $userentry['QB']['school_locked'];
                }
                if($schoolLocked == false) {
                    $userentry['Userentry']['qb_id'] = $data['Userentry']['qb_id'];
                }
                $schoolLocked = false;
                if(isset($userentry['RB1']['school_locked'])) {
                    $schoolLocked = $userentry['RB1']['school_locked'];
                }
                if($schoolLocked == false) {
                    $userentry['Userentry']['rb1_id'] = $data['Userentry']['rb1_id'];
                }
                $schoolLocked = false;
                if(isset($userentry['RB2']['school_locked'])) {
                    $schoolLocked = $userentry['RB2']['school_locked'];
                }
                if($schoolLocked == false) {
                    $userentry['Userentry']['rb2_id'] = $data['Userentry']['rb2_id'];
                }
                $schoolLocked = false;
                if(isset($userentry['WR1']['school_locked'])) {
                    $schoolLocked = $userentry['WR1']['school_locked'];
                }
                if($schoolLocked == false) {
                    $userentry['Userentry']['wr1_id'] = $data['Userentry']['wr1_id'];
                }
                $schoolLocked = false;
                if(isset($userentry['WR2']['school_locked'])) {
                    $schoolLocked = $userentry['WR2']['school_locked'];
                }
                if($schoolLocked == false) {
                    $userentry['Userentry']['wr2_id'] = $data['Userentry']['wr2_id'];
                }
                $schoolLocked = false;
                if(isset($userentry['K']['school_locked'])) {
                    $schoolLocked = $userentry['K']['school_locked'];
                }
                if($schoolLocked == false) {
                    $userentry['Userentry']['k_id'] = $data['Userentry']['k_id'];
                }
                $schoolLocked = false;
                if(isset($userentry['F']['school_locked'])) {
                    $schoolLocked = $userentry['F']['school_locked'];
                }
                if($schoolLocked == false) {
                    $userentry['Userentry']['f_id'] = $data['Userentry']['f_id'];
                }
                $schoolLocked = false;
                if(isset($userentry['D']['school_locked'])) {
                    $schoolLocked = $userentry['D']['school_locked'];
                }
                if($schoolLocked == false) {
                    $userentry['Userentry']['d_id'] = $data['Userentry']['d_id'];
                }

                if ($this->Userentry->save($userentry)) {
                    $this->Session->setFlash(__('Your entry has been updated.'));
                    return $this->redirect(array('action' => 'index'));
                }
            }
        }

        public function add($weekId, $start = 0, $size = 25) {
            if($size > 25) {
                $size = 25;
            }
            if (!$weekId) { throw new NotFoundException(__('Invalid week')); }

            $this->Player = ClassRegistry::init('Player');
            $this->Week = ClassRegistry::init('Week');
            $this->School = ClassRegistry::init('School');
            $this->Playerentry = ClassRegistry::init('Playerentry');

            $userentry = $this->getUserentry($weekId);

            $week = $this->Week->find('first', array('conditions' => array('id' => $weekId), 'recursive' => -1));
            $players = $this->Player->getAvailablePlayers();

            if($this->request->action == 'follow') {
                echo "if<br>";
            } else if ($this->request->is('post')) {
                echo "else if<br>";
                if(empty($userentry)) {
                    $userentry = $this->Userentry->create();
                    $userentry['Userentry']['week_id'] = $weekId;
                    $userentry['Userentry']['user_id'] = $this->Auth->user('id');
                    $userentry['Userentry']['playoff_fl'] = $week['Week']['playoff_fl'];
                }
                $userentry['Userentry']['qb_id'] = $this->request->data['QB_id'];
                $userentry['Userentry']['rb1_id'] = $this->request->data['RB1_id'];
                $userentry['Userentry']['rb2_id'] = $this->request->data['RB2_id'];
                $userentry['Userentry']['wr1_id'] = $this->request->data['WR1_id'];
                $userentry['Userentry']['wr2_id'] = $this->request->data['WR2_id'];
                $userentry['Userentry']['f_id'] = $this->request->data['F_id'];
                $userentry['Userentry']['k_id'] = $this->request->data['K_id'];
                $userentry['Userentry']['d_id'] = $this->request->data['D_id'];

                if ($this->Userentry->save($userentry)) {
                    $this->Session->setFlash(__('Your picks has been saved.'));
                    // reselect to re-fetch the associations
                    $userentry = $this->getUserentry($weekId);
                } else {
                    $userentry = $this->copyPlayerDataFromPlayers($players, $userentry);
                    //debug($this->Userentry->validationErrors);
                }
            }

            $playerentries = array();
            if(isset($userentry['Userentry'])) {
                $playerentries = $this->Playerentry->getPlayerentries($userentry['Userentry']);
            }
            $userentries = $this->Userentry->calculatePreviousUserEntries($weekId, $week['Week']['playoff_fl'], $this->Auth->user('id'));
            $schedule = $this->getGamesSchedule($weekId);
            $schools = $this->School->find('list', array('recursive' => -1));
            $this->set('players', $players);
            $this->set('userentry', $userentry);
            $this->set('userentries', $userentries);
            $this->set('schedule', $schedule);
            $this->set('schools', $schools);
            $this->set('playerentries', $playerentries);
            $this->set('selections', $this->buildPlayerSelections($userentry, $playerentries));
            //$playerData = $this->Player->printPlayersData($start, $size, $players, $userentries, $schedule, $schools);
            //$this->set('playerData', $playerData);
        }

        private function getGamesSchedule($weekId) {
            $this->Game = ClassRegistry::init('Game');
            return $this->Game->getGamesByWeek($weekId);
        }

        private function copyPlayerDataFromPlayers($players, $userentry) {
            $userentry = $this->copyPlayerData('qb', '', $players, $userentry);
            $userentry = $this->copyPlayerData('rb', '1', $players, $userentry);
            $userentry = $this->copyPlayerData('rb', '2', $players, $userentry);
            $userentry = $this->copyPlayerData('wr', '1', $players, $userentry);
            $userentry = $this->copyPlayerData('wr', '2', $players, $userentry);
            $userentry = $this->copyPlayerData('f', '', $players, $userentry);
            $userentry = $this->copyPlayerData('k', '', $players, $userentry);
            $userentry = $this->copyPlayerData('d', '', $players, $userentry);
            return $userentry;
        }

        private function copyPlayerData($position, $secondaryPosition, $players, $userentry) {
            if($userentry['Userentry'][$position.$secondaryPosition.'_id'] != null && $userentry['Userentry'][$position.$secondaryPosition.'_id'] != "") {
                $userentry[strtoupper($position).$secondaryPosition] = $players[strtoupper($position)][$userentry['Userentry'][$position.$secondaryPosition.'_id']]['Player'];
                $userentry[strtoupper($position).$secondaryPosition]['School'] = $players[strtoupper($position)][$userentry['Userentry'][$position.$secondaryPosition.'_id']]['Player']['School'];
            }
            return $userentry;
        }

        private function getUserentry($weekId) {
            $this->Userentry->unbindModel(array('belongsTo' => array('User', 'Week')));
            $this->Userentry->QB->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->RB1->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->RB2->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->WR1->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->WR2->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->F->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->K->unbindModel(array('hasMany' => array('Playerentry')));
            $this->Userentry->D->unbindModel(array('hasMany' => array('Playerentry')));

            $userentry = $this->Userentry->find('first', array('conditions' => array('week_id' => $weekId, 'user_id' => $this->Auth->user('id')), 'recursive' => 2));
            return $userentry;
        }



        public function view($UserId) {
            if (!$UserId) {
                throw new NotFoundException(__('Invalid User'));
            }

            $this->Userentry->User->recursive=-1;
            $User = $this->Userentry->User->findById($UserId, array('name', 'owner'));
            $this->set('title', $User['User']['name']." (".$User['User']['owner'].")");

            $this->Standing = ClassRegistry::init('Standing');
            $this->Standing->unbindModel(array('belongsTo' => array('User')));
            $this->set('records', $this->Standing->find('all', array('conditions' => array('user_id' => $UserId/* , 'Week.lock_time < NOW()' */))));
        }

        public function detail($UserId, $weekId) {
            if (!$UserId) {
                throw new NotFoundException(__('Invalid User'));
            }
            if (!$weekId) {
                throw new NotFoundException(__('Invalid weekId'));
            }

            $this->Week = ClassRegistry::init('Week');
            $this->Playerentry = ClassRegistry::init('Playerentry');

            $record = $this->Userentry->find('first', array('conditions' => array('user_id' => $UserId, 'week_id' => $weekId)));

            if($this->Auth->user('id') != $UserId) {
                $week = $this->Week->find('first', array('conditions' => array('id' => $weekId), 'recursive' => -1));

                $lockedTime = strtotime($week['Week']['lock_time']);
                if(time() - $lockedTime < 0) {

                    foreach ($record as $key => $val) {
                        if($key != 'Userentry' && $key != 'Week' && $key != 'User') {
                            switch($key) {
                                    case "QB":
                                        $translatedKey = "qb_id";
                                        break;
                                    case "RB1":
                                        $translatedKey = "rb1_id";
                                        break;
                                    case "RB2":
                                        $translatedKey = "rb2_id";
                                        break;
                                    case "WR1":
                                        $translatedKey = "wr1_id";
                                        break;
                                    case "WR2":
                                        $translatedKey = "wr2_id";
                                        break;
                                    case "F":
                                        $translatedKey = "f_id";
                                        break;
                                    case "K":
                                        $translatedKey = "k_id";
                                        break;
                                    case "D":
                                        $translatedKey = "d_id";
                                        break;
                                }

                            if(!in_array($val['school'], array_keys($locks))) {
                                $record[$key] = "";
                                $record['Userentry'][$translatedKey] = "";
                            }
                            if(isset($locks[$val['school']]) && (time() - strtotime($locks[$val['school']])) < 0) {
                                $record[$key] = "";
                                $record['Userentry'][$translatedKey] = "";
                            }
                        }
                    }
                }
            }

            $this->set('title', $record['User']['name']." (".$record['User']['owner'].") - Week ".$record['Week']['name']);
            $this->set('record', $record);

            $playerEntries = $this->Playerentry->getplayerentries($record['Userentry']);
            $this->set('playerentries', $playerEntries);

            $playersArray = array($record['Userentry']['qb_id'],$record['Userentry']['rb1_id'],$record['Userentry']['rb2_id'],$record['Userentry']['wr1_id'],$record['Userentry']['wr2_id'],$record['Userentry']['f_id'],$record['Userentry']['k_id'],$record['Userentry']['d_id']);
            $points = $this->Playerentry->getTotalPointsByWeek($record['Userentry']['week_id'], $playersArray);
            $calculatedPoints = $points['points'] == "" ? "0" : $points['points'];
            $this->set('totalPoints', $calculatedPoints);
        }

    private function buildPlayerSelections($userentry, $playerentries) {
        $selections = array();
        $selections['QB'] = $this->getPlayerSelection($userentry, $playerentries, 'QB');
        $selections['RB1'] = $this->getPlayerSelection($userentry, $playerentries, 'RB1');
        $selections['RB2'] = $this->getPlayerSelection($userentry, $playerentries, 'RB2');
        $selections['WR1'] = $this->getPlayerSelection($userentry, $playerentries, 'WR1');
        $selections['WR2'] = $this->getPlayerSelection($userentry, $playerentries, 'WR2');
        $selections['F'] = $this->getPlayerSelection($userentry, $playerentries, 'F');
        $selections['K'] = $this->getPlayerSelection($userentry, $playerentries, 'K');
        $selections['D'] = $this->getPlayerSelection($userentry, $playerentries, 'D');
        return $selections;
    }

    private function getPlayerSelection($userentry, $playerentries, $position) {
        $value = array();
        if(isset($userentry[$position]) && isset($userentry[$position]['School'])) {
            $value['name'] = $userentry[$position]['name']. ', '.$userentry[$position]['position']. ', '.$userentry[$position]['School']['name'];
        }
                if(!empty($playerentries[$position])) {
                        $value['Playerentry'] = $playerentries[$position]['Playerentry'];
                        $value['Playerentry']['stats'] = $this->getPlayerSelectionStatsTable($playerentries[$position]['Playerentry'], $position);
                }
        return $value;
    }

        private function getPlayerSelectionStatsTable($playerentry, $position) {
                $html = "<table><thead>";

                switch($position) {
                    case "QB":
                    case "RB1":
                    case "RB2":
                    case "WR1":
                    case "WR2":
                    case "F":
                        $html .= "<tr>";
                        $html .= "<th colspan='2'>Pass</th>";
                        $html .= "<th colspan='2'>Rush</th>";
                        $html .= "<th colspan='2'>Rec</th>";
                        $html .= "<th colspan='2'>Return</th>";
                        $html .= "</tr>";

                        $html .= "<tr>";
                        $html .= "<td>Yds</td>";
                        $html .= "<td>Tds</td>";
                        $html .= "<td>Yds</td>";
                        $html .= "<td>Tds</td>";
                        $html .= "<td>Yds</td>";
                        $html .= "<td>Tds</td>";
                        $html .= "<td>Yds</td>";
                        $html .= "<td>Tds</td>";
                        $html .= "</tr>";

                        $html .= "</thead>";

                        $html .= "<tr>";
                        $html .= "<td>".$playerentry['pass_yards']."</td>";
                        $html .= "<td>".$playerentry['pass_tds']."</td>";
                        $html .= "<td>".$playerentry['rush_yards']."</td>";
                        $html .= "<td>".$playerentry['rush_tds']."</td>";
                        $html .= "<td>".$playerentry['receive_yards']."</td>";
                        $html .= "<td>".$playerentry['receive_tds']."</td>";
                        $html .= "<td>".$playerentry['return_yards']."</td>";
                        $html .= "<td>".$playerentry['return_tds']."</td>";
                        $html .= "</tr>";
                        break;
                    case "K":
                        $html .= "<tr>";
                        $html .= "<th colspan='2'>Kicking</th>";
                        $html .= "</tr>";

                        $html .= "<tr>";
                        $html .= "<td>FGs</td>";
                        $html .= "<td>PATs</td>";
                        $html .= "</tr>";

                        $html .= "</thead>";

                        $html .= "<tr>";
                        $html .= "<td>".$playerentry['field_goals']."</td>";
                        $html .= "<td>".$playerentry['pat']."</td>";
                        $html .= "</tr>";
                        break;
                    case "D":
                        $html .= "<tr>";
                        $html .= "<th colspan='5'>Defense</th>";
                        $html .= "</tr>";

                        $html .= "<tr>";
                        $html .= "<td>PA</td>";
                        $html .= "<td>Fumbles</td>";
                        $html .= "<td>INTs</td>";
                        $html .= "<td>Tds</td>";
                        $html .= "<td>Safeties</td>";
                        $html .= "</tr>";

                        $html .= "</thead>";

                        $html .= "<tr>";
                        $html .= "<td>".$playerentry['points_allowed']."</td>";
                        $html .= "<td>".$playerentry['fumble_recovery']."</td>";
                        $html .= "<td>".$playerentry['def_ints']."</td>";
                        $html .= "<td>".$playerentry['def_tds']."</td>";
                        $html .= "<td>".$playerentry['safety']."</td>";
                        $html .= "</tr>";
                        break;
                }
                $html .= "</thead>";
                $html .= "</table>";
                return $html;
        }

        public function beforeFilter() {
                $this->Auth->allow('view','detail');
        }

    public function test() {
        $this->Player = ClassRegistry::init('Player');
        $this->Week = ClassRegistry::init('Week');
        $this->School = ClassRegistry::init('School');
        $this->Playerentry = ClassRegistry::init('Playerentry');

        $weekId = 14;
        $userId = 1;
        $userentry = $this->getUserentry($weekId);
        $players = $this->Player->getAvailablePlayers();
        $schedule = $this->getGamesSchedule($weekId);
        /* $schools = $this->School->find('list', array('recursive' => -1));*/
        $week = $this->Week->find('first', array('conditions' => array('id' => $weekId), 'recursive' => -1));
        $userentries = $this->Userentry->calculatePreviousUserEntries($weekId, $week['Week']['playoff_fl'], $userId);

        $playerentries = array();
        if(isset($userentry['Userentry'])) {
            $playerentries = $this->Playerentry->getPlayerentries($userentry['Userentry']);
        }

        $this->set('userentry', json_encode($userentry, JSON_HEX_APOS));
        $this->set('players', json_encode($players, JSON_HEX_APOS));
        $this->set('playerentries', json_encode($playerentries, JSON_HEX_APOS));
    }

    public function getPlayerData($weekId, $userId, $position) {
        $layout = 'ajax'; //<-- No LAYOUT VERY IMPORTANT!!!!!
        $this->autoRender = false;  // <-- NO RENDER THIS METHOD HAS NO VIEW VERY IMPORTANT!!!!!
        $this->Player = ClassRegistry::init('Player');
        $this->Week = ClassRegistry::init('Week');
        $this->School = ClassRegistry::init('School');

        //$userentry = $this->getUserentry($weekId);
        $players = $this->Player->getAvailablePlayers();
        $schedule = $this->getGamesSchedule($weekId);
        $schools = $this->School->find('list', array('recursive' => -1));
        $week = $this->Week->find('first', array('conditions' => array('id' => $weekId), 'recursive' => -1));
        $userentries = $this->Userentry->calculatePreviousUserEntries($weekId, $week['Week']['playoff_fl'], $userId);

        $data = array();
        // loop through all the player records and build the json array
        foreach($players[$position] as $player) {
            $opponentID = $this->getOpponentID($player, $schedule);
            $opponent = "";
            if($opponentID != "") {
                $opponent = $schools[$opponentID];
            }

            $button = '';
            if(!isset($userentries[$position][$player['Player']['id']])) {
                $button = $this->getButton($player, $schedule);
            }

            array_push($data,
                array(
                    $button,
                    $player['Player']['name'].'<br/>'.$this->getPlayerSchool($player),
                    $opponent,
                    $player[0]['SUM(points)'],
                    $player[0]['SUM(pass_yards)'],
                    $player[0]['SUM(pass_tds)'],
                    $player[0]['SUM(rush_yards)'],
                    $player[0]['SUM(rush_tds)'],
                    $player[0]['SUM(receive_yards)'],
                    $player[0]['SUM(receive_tds)'],
                    $player[0]['SUM(return_yards)'],
                    $player[0]['SUM(return_tds)'],
                    $player[0]['SUM(field_goals)'],
                    $player[0]['SUM(pat)'],
                    $player[0]['SUM(points_allowed)'],
                    $player[0]['SUM(fumble_recovery)'],
                    $player[0]['SUM(def_ints)'],
                    $player[0]['SUM(def_tds)'],
                    $player[0]['SUM(safety)']
                    )
                );
        }
        $json = '{"data":'.json_encode($data).'}';
        CakeLog::write('debug',  $json);
        return $json;
    }

    private function getButton($player, $schedule) {
        $buttonId = $player['Player']['id'];
        $buttonLabel = $this->getButtonLabel($player, $schedule);
        $disabled = $this->getDisabledAttribute($buttonLabel);
        $button = '<button id="'.$buttonId.'"'.$disabled.' class="select-player" onclick="selectPlayer()">'.$buttonLabel.'</button>';
        return $button;
    }

    private function getButtonLabel($player, $schedule) {
        $label = 'Select';
        if(isset($schedule[$player['Player']['school_id']])) {
            $game = $schedule[$player['Player']['school_id']]['Game'];
            $lockedTime = strtotime($game['time']) - 10 * 60;
            if(time() > $lockedTime) {
                $label = "Locked";
            }
        } else {
            $label = "Inactive";
        }
        return $label;
    }

    private function getDisabledAttribute($buttonLabel) {
        $class = '';
        if('Select' != $buttonLabel) {
            $class = " disabled='disabled'";
        }
        return $class;
    }

    private function getOpponentID($player, $schedule) {
        if(isset($schedule[$player['Player']['school_id']])) {
            $awaySchoolId = $schedule[$player['Player']['school_id']]['Game']['away_school_id'];
            $homeSchoolId = $schedule[$player['Player']['school_id']]['Game']['home_school_id'];
            if($player['Player']['school_id'] == $awaySchoolId) {
                $schoolId = $homeSchoolId;
            } else {
                $schoolId = $awaySchoolId;
            }
            return $schoolId;
        }
        return "";
    }
    private function getPlayerSchool($player) {
        if(isset($player['Player']['School']['name'])) {
            return $player['Player']['School']['name'];
        }
        return "";
    }
}
?>