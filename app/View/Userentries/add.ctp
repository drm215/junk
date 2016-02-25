<?php
    $errors = getValidationErrors($this->validationErrors);
    $total = calculateTotal($playerentries);
    echo $this->Form->create("Userentry");
?>
<div style="width:33%; display:inline-block;vertical-align: top">
    <table>
        <thead>
            <tr>
                <td colspan="3"/>
            </tr>
            <tr>
                <th>Position</th>
                <th>Player</th>
                <th>Points</th>
            </tr>
        </thead>
        <tbody>
            <?php printPositionSelection($this->Html, $selections, $playerentries, 'QB', $players, $userentry, $errors); ?>
            <?php printPositionSelection($this->Html, $selections, $playerentries, 'RB1', $players, $userentry, $errors); ?>
            <?php printPositionSelection($this->Html, $selections, $playerentries, 'RB2', $players, $userentry, $errors); ?>
            <?php printPositionSelection($this->Html, $selections, $playerentries, 'WR1', $players, $userentry, $errors); ?>
            <?php printPositionSelection($this->Html, $selections, $playerentries, 'WR2', $players, $userentry, $errors); ?>
            <?php printPositionSelection($this->Html, $selections, $playerentries, 'F', $players, $userentry, $errors); ?>
            <?php printPositionSelection($this->Html, $selections, $playerentries, 'K', $players, $userentry, $errors); ?>
            <?php printPositionSelection($this->Html, $selections, $playerentries, 'D', $players, $userentry, $errors); ?>
            <tr>
                <td>Total</td>
                <td/>
                <td><?php echo $total; ?></td>
            </tr>
        </tbody>
    </table>
</div><div style="width:50%; display:inline-block;">
    <?php echo $playerData; ?>
        </div>

    <input id="QB_id" type="hidden" value="<?php echo isset($userentry['Userentry']['qb_id']) ? $userentry['Userentry']['qb_id'] : ""; ?>"/>
    <input id="RB1_id" type="hidden" value="<?php echo isset($userentry['Userentry']['rb1_id']) ? $userentry['Userentry']['rb1_id'] : ""; ?>"/>
    <input id="RB2_id" type="hidden" value="<?php echo isset($userentry['Userentry']['rb2_id']) ? $userentry['Userentry']['rb2_id'] : ""; ?>"/>
    <input id="WR1_id" type="hidden" value="<?php echo isset($userentry['Userentry']['wr1_id']) ? $userentry['Userentry']['wr1_id'] : ""; ?>"/>
    <input id="WR2_id" type="hidden" value="<?php echo isset($userentry['Userentry']['wr2_id']) ? $userentry['Userentry']['wr2_id'] : ""; ?>"/>
    <input id="F_id" type="hidden" value="<?php echo isset($userentry['Userentry']['f_id']) ? $userentry['Userentry']['f_id'] : ""; ?>"/>
    <input id="K_id" type="hidden" value="<?php echo isset($userentry['Userentry']['k_id']) ? $userentry['Userentry']['k_id'] : ""; ?>"/>
    <input id="D_id" type="hidden" value="<?php echo isset($userentry['Userentry']['d_id']) ? $userentry['Userentry']['d_id'] : ""; ?>"/>
<?php echo $this->Form->end('Submit'); ?>

<?php




    function printPositionSelection($html, $selections, $playerentries, $position, $players, $userentry, $errors) {
        $tdClass = "";
        $hasError = false;
        if($errors[strtolower($position).'_id'] != "") {
           $hasError = true;
           $tdClass = "class=\"input error select\"";
        }

                $name = isset($selections[$position]['name']) ? $selections[$position]['name'] : "";
                $title = buildToolTipTable($selections[$position]);
        echo "<tr>";
        echo "<td><a href=\"#\" id=\"show".$position."\">".$position."</a></td>";
        echo "<td ".$tdClass."><div id=\"".$position."_name\" title=\"".$title."\">".$name."</div>";
        if($hasError) {
            echo $html->div('error-message', $errors[strtolower($position).'_id']);
        }
        echo "</td>";
        echo "<td>";

        $points = "-";
        if(isset($playerentries[$position])) {
            $playerentry = $playerentries[$position];
            if(!empty($playerentry)) {
                $points = $playerentry['Playerentry']['points'];
            }
        }
        echo $points;
        echo "</td>";
        echo "</tr>";
    }

    function getValidationErrors($validationErrors) {
        $errors = array();
        $errors['qb_id'] = getValidationError($validationErrors['Userentry'], 'qb_id');
        $errors['rb1_id'] = getValidationError($validationErrors['Userentry'], 'rb1_id');
        $errors['rb2_id'] = getValidationError($validationErrors['Userentry'], 'rb2_id');
        $errors['wr1_id'] = getValidationError($validationErrors['Userentry'], 'wr1_id');
        $errors['wr2_id'] = getValidationError($validationErrors['Userentry'], 'wr2_id');
        $errors['f_id'] = getValidationError($validationErrors['Userentry'], 'f_id');
        $errors['k_id'] = getValidationError($validationErrors['Userentry'], 'k_id');
        $errors['d_id'] = getValidationError($validationErrors['Userentry'], 'd_id');
        return $errors;
    }

    function getValidationError($array, $key) {
        $value = "";
        if(isset($array[$key])) {
            $value = $array[$key][0];
        }
        return $value;
    }

    function calculateTotal($playerentries) {
        $qbPoints = 0;
        $rb1Points = 0;
        $rb2Points = 0;
        $wr1Points = 0;
        $wr2Points = 0;
        $fPoints = 0;
        $kPoints = 0;
        $dPoints = 0;
        if(!empty($playerentries)) {
            $qbPoints = getPoints($playerentries, 'QB');
            $rb1Points = getPoints($playerentries, 'RB1');
            $rb2Points = getPoints($playerentries, 'RB2');
            $wr1Points = getPoints($playerentries, 'WR1');
            $wr2Points = getPoints($playerentries, 'WR2');
            $fPoints = getPoints($playerentries, 'F');
            $kPoints = getPoints($playerentries, 'K');
            $dPoints = getPoints($playerentries, 'D');
        }
        return $qbPoints + $rb1Points + $rb2Points + $wr1Points + $wr2Points + $fPoints + $kPoints + $dPoints;
    }

    function getPoints($array, $key) {
        $value = 0;
        if(!empty($array[$key])) {
            $value = $array[$key]['Playerentry']['points'];
        }
        return $value;
    }

        function buildToolTipTable($selection) {
                $html = "No Stats";
                if(!empty($selection) && isset($selection['Playerentry'])) {
                        $html = $selection['Playerentry']['stats'];
                }
                return $html;
        }
?>
<script>
$(document).ready(function(){
    $("#RB1").hide();
    $("#RB2").hide();
    $("#WR1").hide();
    $("#WR2").hide();
    $("#F").hide();
    $("#K").hide();
    $("#D").hide();

    $("#UserentryAddForm").submit( function(eventObj) {
        $('<input/>').attr('type', 'hidden')
            .attr('name', "QB_id")
            .attr('value', $("#QB_id").val())
            .appendTo('#UserentryAddForm');
        $('<input/>').attr('type', 'hidden')
        .attr('name', "RB1_id")
        .attr('value', $("#RB1_id").val())
        .appendTo('#UserentryAddForm');
        $('<input/>').attr('type', 'hidden')
        .attr('name', "RB2_id")
        .attr('value', $("#RB2_id").val())
        .appendTo('#UserentryAddForm');
        $('<input/>').attr('type', 'hidden')
        .attr('name', "WR1_id")
        .attr('value', $("#WR1_id").val())
        .appendTo('#UserentryAddForm');
        $('<input/>').attr('type', 'hidden')
        .attr('name', "WR2_id")
        .attr('value', $("#WR2_id").val())
        .appendTo('#UserentryAddForm');
        $('<input/>').attr('type', 'hidden')
        .attr('name', "F_id")
        .attr('value', $("#F_id").val())
        .appendTo('#UserentryAddForm');
        $('<input/>').attr('type', 'hidden')
        .attr('name', "K_id")
        .attr('value', $("#K_id").val())
        .appendTo('#UserentryAddForm');
        $('<input/>').attr('type', 'hidden')
        .attr('name', "D_id")
        .attr('value', $("#D_id").val())
        .appendTo('#UserentryAddForm');
        return true;
    });

    $(".select").click(function(){
        $playerId = this.id;
        $playerName = $(eval('"#id_' + $playerId + '"')).text();
        $array = $playerId.split("_");
        $(eval('"#'+ $array[0] + '_name"')).text($playerName);
        $(eval('"#'+ $array[0] + '_id"')).val($array[1]);
    });
    $("#showQB").click(function(){
        $("#QB").show();
        $("#RB1").hide();
        $("#RB2").hide();
        $("#WR1").hide();
        $("#WR2").hide();
        $("#F").hide();
        $("#K").hide();
        $("#D").hide();
    });
    $("#showRB1").click(function(){
        $("#QB").hide();
        $("#RB1").show();
        $("#RB2").hide();
        $("#WR1").hide();
        $("#WR2").hide();
        $("#F").hide();
        $("#K").hide();
        $("#D").hide();
    });
    $("#showRB2").click(function(){
        $("#QB").hide();
        $("#RB1").hide();
        $("#RB2").show();
        $("#WR1").hide();
        $("#WR2").hide();
        $("#F").hide();
        $("#K").hide();
        $("#D").hide();
    });
    $("#showWR1").click(function(){
        $("#QB").hide();
        $("#RB1").hide();
        $("#RB2").hide();
        $("#WR1").show();
        $("#WR2").hide();
        $("#F").hide();
        $("#K").hide();
        $("#D").hide();
    });
    $("#showWR2").click(function(){
        $("#QB").hide();
        $("#RB1").hide();
        $("#RB2").hide();
        $("#WR1").hide();
        $("#WR2").show();
        $("#F").hide();
        $("#K").hide();
        $("#D").hide();
    });
    $("#showF").click(function(){
        $("#QB").hide();
        $("#RB1").hide();
        $("#RB2").hide();
        $("#WR1").hide();
        $("#WR2").hide();
        $("#F").show();
        $("#K").hide();
        $("#D").hide();
    });
    $("#showK").click(function(){
        $("#QB").hide();
        $("#RB1").hide();
        $("#RB2").hide();
        $("#WR1").hide();
        $("#WR2").hide();
        $("#F").hide();
        $("#K").show();
        $("#D").hide();
    });
    $("#showD").click(function(){
        $("#QB").hide();
        $("#RB1").hide();
        $("#RB2").hide();
        $("#WR1").hide();
        $("#WR2").hide();
        $("#F").hide();
        $("#K").hide();
        $("#D").show();
    });
    $(function () {
      $(document).tooltip({
          content: function () {
              return $(this).prop('title');
          }
      });
  });
});
</script>

