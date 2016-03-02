<div id="wrapper">
    <div id="contentliquid"><div id="contentwrap">
    <div id="content">

        <table id="player-data" class="display compact" style="width: 100%">
            <thead>
                <tr>
                    <th rowspan="2"></th>
                    <th rowspan="2">Player</th>
                    <th rowspan="2" style="width: 45px">Opponent</th>
                    <th rowspan="2">Points</th>
                    <th colspan="2">Passing</th>
                    <th colspan="2">Rushing</th>
                    <th colspan="2">Receiving</th>
                    <th colspan="2">Return</th>
                    <th colspan="2">Kicking</th>
                    <th colspan="5">Defense</th>
                </tr>
                <tr>
                    <th class="offense">Yards</th>
                    <th class="offense">TDs</th>
                    <th class="offense">Yards</th>
                    <th class="offense">TDs</th>
                    <th class="offense">Yards</th>
                    <th class="offense">TDs</th>
                    <th class="offense">Yards</th>
                    <th class="offense">TDs</th>
                    <th class="kicking">FGs</th>
                    <th class="kicking">PATs</th>
                    <th class="defense">PA</th>
                    <th class="defense">Fumbles</th>
                    <th class="defense">INTs</th>
                    <th class="defense">TDs</th>
                    <th class="defense">Safeties</th>
                </tr>
            </thead>
        </table>

    </div>
    </div></div>
    <div id="leftcolumnwrap">
    <div id="leftcolumn">

        <table id="selection-data" style="width: 250px; table-layout: fixed;">
            <thead>
                <tr>
                    <th style="width: 20%">Pos</th>
                    <th style="width: 60%">Player</th>
                    <th style="width: 20%;">Points</th>
                </tr>
            </thead>
            <tbody>
                <tr><td><a class="toggle-position" id="QB">QB</a></td><td id="qb-player-name"></td><td id="qb-player-points" style="text-align:right"></td></tr>
                <tr><td><a class="toggle-position" id="RB1">RB1</a></td><td id="rb1-player-name"></td><td id="rb1-player-points" style="text-align:right"></td></tr>
                <tr><td><a class="toggle-position" id="RB2">RB2</a></td><td id="rb2-player-name"></td><td id="rb2-player-points" style="text-align:right"></td></tr>
                <tr><td><a class="toggle-position" id="WR1">WR1</a></td><td id="wr1-player-name"></td><td id="wr1-player-points" style="text-align:right"></td></tr>
                <tr><td><a class="toggle-position" id="WR2">WR2</a></td><td id="wr2-player-name"></td><td id="wr2-player-points" style="text-align:right"></td></tr>
                <tr><td><a class="toggle-position" id="F">F</a></td><td id="f-player-name"></td><td id="f-player-points" style="text-align:right"></td></tr>
                <tr><td><a class="toggle-position" id="K">K</a></td><td id="k-player-name"></td><td id="k-player-points" style="text-align:right"></td></tr>
                <tr><td><a class="toggle-position" id="D">D</a></td><td id="d-player-name"></td><td id="d-player-points" style="text-align:right"></td></tr>
                <tr><td><strong>Total</strong></td><td></td><td id="total-player-points" style="text-align:right"></td></tr>
            </tbody>
        </table>
    </div>
    </div>
</div>




<script>
$(document).ready(function() {
    var playerData = {
        updateColumnVisibility: function() {
            var $position = $('#hidden-position').val();
            if($position === 'D') {
                $('#player-data').DataTable().columns('.offense').visible(false);
                $('#player-data').DataTable().columns('.kicking').visible(false);
                $('#player-data').DataTable().columns('.defense').visible(true);
            } else if($position === 'K') {
                $('#player-data').DataTable().columns('.offense').visible(false);
                $('#player-data').DataTable().columns('.kicking').visible(true);
                $('#player-data').DataTable().columns('.defense').visible(false);
            } else {
                $('#player-data').DataTable().columns('.offense').visible(true);
                $('#player-data').DataTable().columns('.kicking').visible(false);
                $('#player-data').DataTable().columns('.defense').visible(false);
            }
        },
        updatePlayerDataTable: function(position) {
            $('#hidden-position').val(playerData.getBasePosition(position));
            //console.log($('#hidden-position').val());
            $('#player-data').DataTable().ajax.url(playerData.getAjaxUrl()).load();
        },
        getAjaxUrl: function() {
            return "/challenge/userentries/getPlayerData/" + $('#hidden-weekId').val() + "/" + $('#hidden-userId').val() + "/" + $('#hidden-position').val();
        },
        getBasePosition: function(position) {
            var basePosition = position;
            if(position === 'RB1' || position === 'RB2') {
                basePosition = 'RB';
            } else if(position === 'WR1' || position === 'WR2') {
                basePosition = 'WR';
            }
            return basePosition;
        },
        setSelectedPlayers: function(userentry, playerentries) {
            var totalPoints = 0;
            var positions = ["QB", "RB1", "RB2", "WR1", "WR2", "F", "K", "D"];
            for (var i = 0; i < positions.length; i++) {
                if(userentry[positions[i]]["id"]) {
                    $('#' + positions[i].toLowerCase() + '-id').val(userentry[positions[i]]["id"]);
                    $('#' + positions[i].toLowerCase() + '-player-name').text(userentry[positions[i]]["name"]);// + '\n' + userentry[positions[i]]["School"]["name"]);
                    if(playerentries[positions[i]]['Playerentry']) {
                        $('#' + positions[i].toLowerCase() + '-player-points').text(playerentries[positions[i]]['Playerentry']['points']);
                        totalPoints += parseFloat(playerentries[positions[i]]['Playerentry']['points']);
                    }
                }
                $('#total-player-points').text(totalPoints);
            }
        },
        selectPlayer: function() {
            console.log("selectPlayer");
        }
    };

    $('#player-data').DataTable({
        "ajax": {
            "url": playerData.getAjaxUrl()
         },
         "dataType": "json",
         "order": [3, 'desc']
    });

    $('#selection-data').DataTable({
        bFilter: false,
        paging: false,
        bInfo: false,
        bSort: false,
        autoWidth: false
    });
    var userentry = JSON.parse('<?php echo $userentry; ?>');
    var playerentries = JSON.parse('<?php echo $playerentries; ?>');

    playerData.updateColumnVisibility();
    playerData.setSelectedPlayers(userentry, playerentries);

    $('.toggle-position').on('click', function() {
        playerData.updatePlayerDataTable($(this).attr('id'));
    });
    $('.select-player').on('click', function() {
        console.log($(this).attr('id'));
    });

    function selectPlayer() {
    	playerData.selectPlayer()
    }
});
</script>

<input id="qb-id" type="hidden"/>
<input id="rb1-id" type="hidden"/>
<input id="rb2-id" type="hidden"/>
<input id="wr1-id" type="hidden"/>
<input id="wr2-id" type="hidden"/>
<input id="f-id" type="hidden"/>
<input id="k-id" type="hidden"/>
<input id="d-id" type="hidden"/>

<input id="hidden-position" value="QB" type="hidden"/>
<input id="hidden-weekId" value="14" type="hidden"/>
<input id="hidden-userId" value="1" type="hidden"/>