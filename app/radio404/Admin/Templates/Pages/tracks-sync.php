<?php

use radio404\Core\RadioKing;

$sync_page_title = __( 'Syncronisation des pistes RadioKing', 'radio404' );
try {
	$track_boxes = RadioKing::get_boxes();
	$count       = $track_boxes[0]->count;

}catch (Exception $err){
	$track_boxes =[];
    $count = 0;
}

?><div class="wrap">
	<h1><?= $sync_page_title ?></h1>

    <select name="track-boxes" id="track-boxes" class="track-boxes-select">

    <?php foreach ($track_boxes as $trackbox){

        $names = [
          '__MUSIC__' => 'Musique',
          '__IDENTIFICATION__' => 'Habillage radio',
          '__PODCAST__' => 'Podcast',
          '__AD__' => 'Publicité',
          '__CHRONIC__' => 'Chronique',
          '__DEDICATION__' => 'Dédicace',
        ];
        $disabled = $trackbox->count===0 ? ' disabled':'';

        $name = $names[$trackbox->name] ?? $trackbox->name;
        echo "<option value='$trackbox->idtrackbox' $disabled data-name='$trackbox->name'>$name ($trackbox->count)</option>";

    } ?>
    </select>

    <select name="days-restriction" id="days-restriction" class="days-restriction">
        <option value="-1">depuis toujours</option>
        <option value="90">depuis 90 jours</option>
        <option value="30">depuis 30 jours</option>
        <option value="7">depuis une semaine</option>
        <option value="3">depuis 3 jours</option>
        <option value="1">depuis hier</option>
        <option value="0">d'aujourd'hui</option>
    </select>

    <button type="button" class="button button-primary start-tracks-sync">Lancer la synchronisation des <span class="trackbox-count"><?= $count ?></span> morceaux</button>
    <span class="sync-progress-label"></span>
    <script>
		var radioking_access_token = "<?= $access_token ?>";
		var track_boxes = <?= json_encode($track_boxes) ?>;
	</script>

    <progress class="tracks-sync-progress" min="0" max="<?= $count ?>" value="0"></progress>

    <div class="tracks-sync-list"></div>

</div>