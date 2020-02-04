<?php

$import_page_title = __( 'Syncronisation du planning RadioKing', 'radio404' );

setlocale(LC_ALL,'fr');

//*/
?><div class="wrap">
	<h1><?= $import_page_title ?></h1>
    <button type="button" class="button button-primary start-schedule-import">Importer et mettre à jour les programmes</button>
    <hr />

    <div class="planning planning--day-name">
        <div class="planning__container planning__container--day-name">
		    <?php for($i=0; $i<7; $i++){
			    $d = ($i+1)-7;
			    $dayName = $dow_text = date('D', strtotime("Sunday +{$d} days"));;
			    $dayDate = $dow_text = date('d/m', strtotime("Sunday +{$d} days"));;
			    echo "<div class='planning__day planning__day-name'>$dayName $dayDate</div>";
		    } ?>
        </div>
    </div>
    <div class="planning">
        <div class="planning__container">
        <?php for($i=0; $i<7; $i++){
            $d = ($i+1)%7;
	        $hours = '';
            for($h=1;$h<24;$h++){
                $top = 100*$h/24;
	            $hours .= "<div class='planning__hour planning__hour--$h' style='top:$top%'></div>";
            }
            echo "<div class='planning__day day-$d' data-day='$d'>$hours</div>";
        } ?>
        </div>
    </div>

</div>