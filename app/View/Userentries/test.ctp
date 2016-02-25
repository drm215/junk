<table id="player-data-QB">
    <thead>
        <tr>
            <th></th>
            <th>Player</th><!--
            <th>Opponent</th>
            <th>Points</th>
            <th>Yards</th>
            <th>TDs</th>
            <th>Yards</th>
            <th>TDs</th>
            <th>Yards</th>
            <th>TDs</th>
            <th>Yards</th>
            <th>TDs</th>
        --></tr>
    </thead>
</table>

<script>
$(document).ready(function() {
    $('#player-data-QB').DataTable({
        "ajax": {
    	    "url": "/challenge/userentries/getPlayerData/14/1",
            "success": function(result) {
                return result;
             }
         },
         "dataType": "json"
    });
});
</script>