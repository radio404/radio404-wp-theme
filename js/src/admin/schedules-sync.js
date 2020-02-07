import { Calendar } from '@fullcalendar/core';
import timeGridPlugin from '@fullcalendar/timegrid';
import dayGridPlugin from '@fullcalendar/daygrid';
import listPlugin from '@fullcalendar/list';
import frLocale from '@fullcalendar/core/locales/fr';

import '@fullcalendar/core/main.css';
import '@fullcalendar/daygrid/main.css';
import '@fullcalendar/timegrid/main.css';
import '@fullcalendar/list/main.css';

import '../../../sass/_schedules-sync.scss';

jQuery(document).ready(function($){

    var calendarEl = document.getElementById('calendar');

    var calendar = new Calendar(calendarEl, {
        locale: frLocale,
        plugins: [ listPlugin, timeGridPlugin, dayGridPlugin ],
        defaultView: 'listWeek',
        header:{
            left:'listWeek,timeGridWeek,dayGridMonth',
            center:'title',
            right:'importTracks prev,next,today'
        },
        customButtons: {
            importTracks: {
                text: 'Importer depuis RadioKing',
                click: importSchedules,
            }
        },
        events:calendarEvents,
        allDaySlot:false
    });

    calendar.render();

    function calendarEvents(info, successCallback, failureCallback){
        loadSchedule('fetch',info).done(function(schedules){
            successCallback(
                schedules.map(mapScheduleToEvent)
            )
        });
    }

    function mapScheduleToEvent(schedule){
        const {idschedule,name,wp_edit_link,color} = schedule,
            schedule_start = new Date(schedule.schedule_start),
            schedule_end = new Date(schedule.schedule_end);
        let classNames = [];

        if(!wp_edit_link){
            classNames.push('no-wp-schedule');
        }
        return {
            id:idschedule,
            title:name,
            url:wp_edit_link,
            start:schedule_start,
            end:schedule_end,
            backgroundColor:color,
            borderColor:color,
            classNames:classNames,
        };
    }

    function loadSchedule(sync_type,info){
        const {start,end} = info;
        return wp.apiRequest({
            method: 'post',
            url: '/wp-json/radio404/v1/schedules/'+sync_type,
            data:{
                start:start/1000,
                end:end/1000
            }
        })
    }

    function importSchedules(event){
        const view = calendar.view,
                info = {
                start:view.activeStart.getTime(),
                end:view.activeEnd.getTime()
            },
            {toElement} = event;
        toElement.disabled = true;
        loadSchedule('import',info).done((schedules)=>{
            toElement.disabled = false;
            schedules.forEach((schedule)=>{
                const {idschedule,wp_edit_link} = schedule,
                    event = calendar.getEventById(idschedule);
                if(event && wp_edit_link){
                    event.setProp('url',wp_edit_link);
                    event.setProp('classNames',[]);
                }
                console.log(schedule,event);
            });
        });
    }

})