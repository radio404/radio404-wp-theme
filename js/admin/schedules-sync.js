jQuery(document).ready(function($){

    var importButton = $('.start-schedule-import');

    function getMonday(d) {
        d = new Date(d);
        var day = d.getDay(),
            diff = d.getDate() - day + (day == 0 ? -6:1); // adjust when day is sunday
        return new Date(d.setDate(diff));
    }

    function renderSchedules(schedules){

        importButton.attr('disabled',false);

        var monday = getMonday(new Date());

        schedules.forEach(function(schedule){
            if(schedule.day_playlist) return;
            var schedule_start = new Date(schedule.schedule_start);
            var schedule_end = new Date(schedule.schedule_end);
            if(schedule_start < monday ) return;
            var schedule_start_day = schedule_start.getDay(),
                schedule_start_time_percent = schedule_start.getHours()/24 + schedule_start.getMinutes()/(24*60),
                schedule_end_time_percent = schedule_end.getHours()/24 + schedule_end.getMinutes()/(24*60),
                schedule_duration_time_percent = schedule_end_time_percent - schedule_start_time_percent,
                top = (schedule_start_time_percent*100).toFixed(2)+'%',
                height = (schedule_duration_time_percent*100).toFixed(2)+'%';
            var scheduleCss = 'top:'+top+';height:'+height+';background:'+schedule.color,
                scheduleHtml = '<div title="'+schedule.name+'" class="planning__schedule" style="'+scheduleCss+'">'+schedule.name+'</div>';
            $('.planning__day.day-'+schedule_start_day).append(scheduleHtml);
            console.log(schedule.name, schedule_start_time_percent, schedule_end_time_percent, schedule);
        })
    }

    function loadSchedule(sync_type){
        importButton.attr('disabled',true);
        wp.apiRequest({
            method: 'post',
            url: '/wp-json/radio404/v1/schedules/'+sync_type,
        }).done(renderSchedules);
    }

    loadSchedule('fetch');

    function importSchedules(){
        loadSchedule('import');
    }

    importButton.click(importSchedules);
})