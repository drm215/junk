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

    public function beforeFilter() {
        $this->Auth->allow('view','detail');
    }

    public function add($weekId) {
        $this->Player = ClassRegistry::init('Player');
        $this->Week = ClassRegistry::init('Week');
        $this->School = ClassRegistry::init('School');
        $this->Playerentry = ClassRegistry::init('Playerentry');

        $userentry = $this->getUserentry($weekId);
        $players = $this->Player->getAvailablePlayers();

        if($this->request->is('post')) {
            if(empty($userentry)) {
                $userentry = $this->Userentry->create();
                $userentry['Userentry']['week_id'] = $weekId;
                $userentry['Userentry']['user_id'] = $this->Auth->user('id');
                $userentry['Userentry']['playoff_fl'] = $week['Week']['playoff_fl'];
            }
            $userentry['Userentry']['qb_id'] = $this->request->data['qb-id'];
            $userentry['Userentry']['rb1_id'] = $this->request->data['rb1-id'];
            $userentry['Userentry']['rb2_id'] = $this->request->data['rb2-id'];
            $userentry['Userentry']['wr1_id'] = $this->request->data['wr1-id'];
            $userentry['Userentry']['wr2_id'] = $this->request->data['wr2-id'];
            $userentry['Userentry']['f_id'] = $this->request->data['f-id'];
            $userentry['Userentry']['k_id'] = $this->request->data['k-id'];
            $userentry['Userentry']['d_id'] = $this->request->data['d-id'];

            //debug($userentry);

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

        $this->set('userentry', json_encode($userentry, JSON_HEX_APOS));
        $this->set('players', json_encode($players, JSON_HEX_APOS));
        $this->set('playerentries', json_encode($playerentries, JSON_HEX_APOS));
    }

    public function getPlayerData($weekId, $userId, $position) {
        CakeLog::write('debug', "getPlayerData:");
        CakeLog::write('debug', "weekId: " . $weekId);
        CakeLog::write('debug', "userId: " . $userId);
        CakeLog::write('debug', "position: " . $position);
        //CakeLog::write('debug',  'getPlayerData: weekId = ' . $weekId . ', userId = ' . $userId . ', position = ' + $position);
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
        $buttonId = 0;
        // loop through all the player records and build the json array
        foreach($players[$position] as $player) {
            $opponentID = $this->getOpponentID($player, $schedule);
            $opponent = "";
            if($opponentID != "") {
                $opponent = $schools[$opponentID];
            }

            $button = '';
            $playerName = $playerName = $player['Player']['name'].'<br/>'.$this->getPlayerSchool($player);
            if(!isset($userentries[$position][$player['Player']['id']])) {
                $button = $this->getButton($player, $schedule, $buttonId);
            } else {
                $playerName = '<span style="text-decoration:line-through">' . $playerName . '</span>';
            }

            array_push($data,
                array(
                    $player['Player']['id'],
                    $player['Player']['position'],
                    $button,
                    $playerName,
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
            $buttonId++;
        }
        $json = '{"data":'.json_encode($data).'}';
        return $json;
    }

    private function getButton($player, $schedule, $buttonId) {
        $buttonLabel = $this->getButtonLabel($player, $schedule);
        $disabled = $this->getDisabledAttribute($buttonLabel);
        $button = '<button id="'.$buttonId.'"'.$disabled.' class="select-player">'.$buttonLabel.'</button>';
        return $button;
    }

    private function getButtonLabel($player, $schedule) {
        $label = "Locked";
        if(empty($schedule)) {
            $label = "Inactive";
        } else if(isset($schedule[$player['Player']['school_id']])) {
            $game = $schedule[$player['Player']['school_id']]['Game'];
            $lockedTime = strtotime($game['time']) - 10 * 60;
            if(time() < $lockedTime) {
                $label = "Select";
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