jQuery(document).ready(function($){
    var buttonStartTracksSync = $('.start-tracks-sync');
    var progress = $('.tracks-sync-progress');
    var trackSyncList = $('.tracks-sync-list');
    var trackBoxesSelect = $('.track-boxes-select');
    var daysRestrictionSelect = $('.days-restriction');
    var daysRestriction = -1;
    var idtrackbox = 1;

    buttonStartTracksSync.click(function(){
        trackBoxesSelect.attr('disabled',true);
        buttonStartTracksSync.attr('disabled',true);
        StartTrackSync();
    })

    trackBoxesSelect.change(function(e){
        var id = trackBoxesSelect.find(':selected').val(),
            trackbox = track_boxes.find(function(t){
                console.log(id,t);
           return t.idtrackbox == id;
        });
        idtrackbox = trackbox.idtrackbox;
        $('.trackbox-count').text(trackbox.count);
        progress.text(trackbox.count);
        progress.attr('max',trackbox.count);
    });

    function StartTrackSync(){
        var total_done = 0, total_todo = progress.attr('max'), startTime = Date.now();
        function TrackSync(offset) {
            wp.apiRequest({
                method: 'post',
                url: '/wp-json/radio404/v1/tracks/sync',
                data: {
                    radioking_access_token: radioking_access_token,
                    idtrackbox: idtrackbox,
                    offset: offset,
                    limit: 5,
                },
                dataType:'json'
            }).done(function (tracks) {
                console.log(tracks)
                total_done += tracks.length;
                var ratio_done = total_done/total_todo,
                    time_done = Date.now()-startTime,
                    time_estimed = (time_done*total_todo/total_done)-time_done;
                $('.sync-progress-label').html('<code>'+(100*ratio_done).toFixed(2)+'%</code>- temps écoulé : '+(time_done/1000).toFixed()+'s  — temps restant estimé : '+(time_estimed/1000).toFixed(0)+'s');
                var output = '';
                tracks.forEach(function(t){
                    output +='<div class="track">';
                    if(t.wp_track) {
                            '<small><code class="wp_track">' + t.wp_track.ID + '</code></small> ';
                        ;
                    }else{
                        output += '<code class="track">Error : '+t.error+'</code> ';
                    }
                    output +=
                        '<span class="title">' + t.track.title + '</span> ' +
                        '<span class="album">' + t.track.album + '</span> ' +
                        '<span class="artist">' + t.track.artist + '</span>' +
                        '</div>';
                });
                trackSyncList.prepend(output);
                if(tracks.length){
                    progress.attr('value',total_done);
                    TrackSync(total_done);
                }else{
                    trackBoxesSelect.attr('disabled',false);
                    buttonStartTracksSync.attr('disabled',false);
                }
            }).fail(function(err,a,status){
                var message = status;
                switch(status){
                    case 'Not Found':
                        break;
                    default:
                        TrackSync(total_done);
                }
                trackSyncList.prepend('<div><code>Error : '+message+'</code></div>');
            });
        }
        TrackSync(0);
    }

})