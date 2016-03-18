<?php
    class PlayersController extends AppController {

        public $components = array('RequestHandler', 'Paginator');

        public $paginate = array(
            'limit' => 25,
            'order' => array(
                'Player.name' => 'asc'
            )
        );

        public function recommend() {
            $playerentries = array();

            $this->Userentry = ClassRegistry::init('Userentry');
            $this->Playerentry = ClassRegistry::init('Playerentry');

            $playerids = array();
            $userentries = $this->Userentry->find('all', array('conditions' => array('user_id' => $this->Auth->user('id')), 'recursive' => -1));
            foreach($userentries as $userentry) {
                if(isset($userentry['Userentry']['qb_id'])) {
                    array_push($playerids, $userentry['Userentry']['qb_id']);
                }
                if(isset($userentry['Userentry']['rb1_id'])) {
                    array_push($playerids, $userentry['Userentry']['rb1_id']);
                }
                if(isset($userentry['Userentry']['rb2_id'])) {
                    array_push($playerids, $userentry['Userentry']['rb2_id']);
                }
                if(isset($userentry['Userentry']['wr1_id'])) {
                    array_push($playerids, $userentry['Userentry']['wr1_id']);
                }
                if(isset($userentry['Userentry']['wr2_id'])) {
                    array_push($playerids, $userentry['Userentry']['wr2_id']);
                }
                if(isset($userentry['Userentry']['f_id'])) {
                    array_push($playerids, $userentry['Userentry']['f_id']);
                }
                if(isset($userentry['Userentry']['k_id'])) {
                    array_push($playerids, $userentry['Userentry']['k_id']);
                }
                if(isset($userentry['Userentry']['d_id'])) {
                    array_push($playerids, $userentry['Userentry']['d_id']);
                }
            }

            $playerentries['QB'] = $this->getPlayerRecommendationsByPosition('QB', $playerids);
            $playerentries['RB'] = $this->getPlayerRecommendationsByPosition('RB', $playerids);
            $playerentries['WR'] = $this->getPlayerRecommendationsByPosition('WR', $playerids);
            $playerentries['TE'] = $this->getPlayerRecommendationsByPosition('TE', $playerids);
            $playerentries['K'] = $this->getPlayerRecommendationsByPosition('K', $playerids);
            $playerentries['D'] = $this->getPlayerRecommendationsByPosition('D', $playerids);

            $this->set('players', $playerentries);
        }

        private function getPlayerRecommendationsByPosition($position, $playerids) {
            $this->Playerentry->unbindModel(array('belongsTo' => array('Week')));
            $playerentries = $this->Playerentry->find('all',
                array(
                    'fields' => array('SUM(Playerentry.points) AS points', 'COUNT(\'x\') as count', 'Player.id', 'Player.name', 'Player.school', 'Player.position'),
                    'group' => array('Player.id'),
                    'conditions' => array('Player.position' => $position, "NOT" => array('Player.id' => $playerids)),
                    'order' => array('SUM(Playerentry.points) / COUNT(\'x\') DESC'),
                    'limit' => 10
                )
            );
            return $playerentries;
        }

        public function duplicates() {
            $duplicates = $this->Player->find('all', array('fields' => array('name', 'COUNT(*) as ct'), 'group' => array('name HAVING COUNT(*) > 1'), 'order' => array('COUNT(*) DESC'), 'conditions' => array('NOT' => array('name' => array('Kickers', 'Defense'))), 'recursive' => -1));
            $this->set('duplicates', $duplicates);
        }

        public function getPlayers($start, $increment, $userId, $weekId, $playoffFlag) {
            $this->set('data', $this->Player->getPlayers($start, $increment, $userId, $weekId, $playoffFlag));
        }

        public function beforeFilter() {
            $this->Auth->allow('view','detail');
        }

        public function index() {
            $this->Paginator->settings = $this->paginate;
            $data = $this->Paginator->paginate('Player');
             $this->set('players', $data);

            //$this->set('players', $this->Player->find('all', array('recursive' => -1)));
        }
      
        /**
        Server side validation that the player is locked.
        **/
        public function isPlayerLocked($id, $position, $weekId) {
            $this->Game = ClassRegistry::init('Game');

            $this->unbindModel(array('hasMany' => array('Playerentry')));
            $this->School->unbindModel(array('hasMany' => array('Player')));
            $player = $this->find('first', array('conditions' => array('Player.id' => $id), 'recursive' => 0));
            if(!empty($player)) {
                $school = $player['School'];

                $game = $this->Game->find('first', array('recursive' => -1, 'conditions' => array('week_id' => $weekId, 'OR' => array('away_school_id' => $player['School']['id'], 'home_school_id' => $player['School']['id']))));
                if(!empty($game)) {
                    $lockedTime = strtotime($game['Game']['time']) - 10 * 60;
                    if(time() > $lockedTime) {
                        return true;
                    }
                }
            }
            return false;
        }
    }
?>