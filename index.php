<?php
session_start();
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <link rel="stylesheet" href="css/main.css">
        <script src="js/main.js" type="text/javascript"></script>
        <link rel="stylesheet" href="css/jquery-ui.css">
        <script src="js/jquery-1.12.4.js" type="text/javascript"></script>
        <script src="js/ui/jquery-ui.js" type="text/javascript"></script>
        <script src="js/jquery.ui.touch-punch.js" type="text/javascript"></script>
          <script>
            $( function() {
               $( "#board" ).draggable({ scroll: false });
              var crfs = new CRFS($("#board"));
            } );
            </script>
    </head>
    <body>
        <div id="board" session="58ae4e6d3d578" playerId="<?php echo($_SESSION['playerId']); ?>" sequence="<?php echo($_SESSION['playerSequence']); ?>">
            <div style="background-color: gray; opacity: 0.4; width: 3000px; height: 3000px; position: absolute;"></div>
        </div>
        <div id="buttons">
            <div id="next" class="tile" tileid="-1" preview="1"><div id="next" class="tile" tileid="-4"></div></div>
            <div class="play" playid="1" id="playNo"></div>
            <div class="play" playid="2" id="playYes"></div>
        </div>
        <div class="scoreBoard"></div>
        <div id="debug" style = "position: absolute; width: 800px; background-color: yellow; "></div>
    </body>
</html>
