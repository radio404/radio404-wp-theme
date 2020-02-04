<?php

$page_title = __( 'Historique des pistes RadioKing des dernières 24h', 'radio404' );

?><div class="wrap">
	<h1><?= $page_title ?></h1>

    <table class="tracks-history__table">
        <thead>
            <tr>
                <th></th>
                <th></th>
                <th>Titre</th>
                <th>Artiste</th>
                <th>Album</th>
                <th>Upload</th>
            </tr>
        </thead>
        <tbody><?php

        try {
            $tracks_history = \radio404\Core\RadioKing::get_tracks_history();
            foreach ($tracks_history as $line){

                $wp_post = $line->wp_post;
	            $wp_post_edit_link     = get_edit_post_link( $wp_post->ID );
	            $wp_album_id           = get_field( 'album', $wp_post->ID );
	            $wp_post_thumbnail     = get_the_post_thumbnail_url( $line->wp_track_id, [ 100, 100 ] );
	            $wp_post_author        = $wp_post->post_author == 0 ? 'un bel inconnu' : get_the_author_meta( 'display_name', $wp_post->post_author );
	            $wp_post_thumbnail_img = $wp_post_thumbnail ?
					"<img class='thumbnail' width='25' height='25' src='$wp_post_thumbnail' alt='' loading='lazy' />" : "";

	            $line->post = $wp_post;
	            $line->post_edit_link = $wp_post_edit_link;
	            $line->post_thumbnail = $wp_post_thumbnail;

	            if ( $wp_album_id ) {
		            $wp_album      = get_post( $wp_album_id );
		            $wp_album_link = get_edit_post_link( $wp_album_id );
		            $album_info    = "<a href='$wp_album_link'>$wp_album->post_title</a>";
	            } else {
		            $album_info = "";
	            }

	            $wp_artist_list = get_field( 'artist', $wp_post->ID );
	            $artist_info    = '';
	            foreach ( $wp_artist_list as $artist ) {
		            $artist_link = get_edit_post_link( $artist->ID );
		            $artist_info .= "<a href='$artist_link'>$artist->post_title</a> ";
	            }
	            $date = new DateTime($line->started_at,$utc_timezone);
	            $date->setTimezone($paris_timezone);
	            $d = $date->format('H:i');

                ?>
                <tr>
                <td class='col-time'><code class='time' title='<?= $line->rk_track_id ?>'><?= $d ?></code></td>
                <?php if($wp_post){ ?>
	            <td class='col-cover'><?= $wp_post_thumbnail_img ?></td>
	            <td class='col-title tracks-history__track-title'><a href='$wp_post_edit_link'><?= $wp_post->post_title ?></a> </td>
	            <td class='col-artist'><?= $artist_info ?></td>
	            <td class='col-album'><?= $album_info ?></td>
	            <td class='col-author'><strong><?= $wp_post_author ?></strong></td>
                <?php }else{ ?>
                <td colspan='5'><?= $line->wp_track_id ?></td>
                <?php } ?>
                </tr>
                <?php
            }
        }catch (Throwable $err){
            echo $err->getMessage();
        }

		?></tbody></table>

</div>