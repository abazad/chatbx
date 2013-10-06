/**
 * Chatbx-Dmz
 *
 * @author    Jhayrdmz (mjigs_dime07@live.com | dmz.gfx@gmail.com)
 * @copyright Copyright (c) 2013
 * @version   1.0
 **/
(function($) {

    var methods = {
        init: function() {

            var chatbx;
            var lastChatID = 0;
            var lastActivity = 0;
            var activeWindow = true;
            var sendingMessage = false;
            var $elemInputMessage = $("#chatbx_input_message input");

            dmzAjax("GET", "ajax/initialize", null, function(data) {
                if($.isEmptyObject(data) == false) {
                    chatbx = data;
                    console.log(data);
                    initChatbx();
                }
            });

            (function getChatsFunction(){
                getChats(getChatsFunction);
            })();

            (function liveTimeFunction(){
                liveTime(liveTimeFunction);
            })();

            function initChatbx() {
                $("#chatbx_connect_btn").live("click", function(e) {
                    e.preventDefault();
                    popupwindow($(this).attr("data-connect"));
                });

                if(!chatbx.user.social_id) return false;

                $(window.parent.window, window).hover(function() {
                    activeWindow = true;
                }).blur(function() {
                    activeWindow = false;
                });

                $("#chatbx_soundfx").click( function() {
                    var lem = ($(this).attr("data") == 'sound-off') ? 'sound-on' : 'sound-off';
                    if(lem == 'sound-on') {
                        $("#chatbx_soundfx i").attr('class', 'icon-volume-up');
                    } else {
                        $("#chatbx_soundfx i").attr('class', 'icon-volume-off');
                    }
                    $(this).attr("data", lem);
                });

                $('#chatbx_form_composer').submit(function(e) {
                   submitForm(e);
                });

                $("#chatbx_send_message").live("click", function(e) {
                    submitForm(e);
                });

                console.log("Ready...");

                // (function getChatsFunction(){
                //     getChats(getChatsFunction);
                // })();

                // (function liveTimeFunction(){
                //     liveTime(liveTimeFunction);
                // })();

            }

            function submitForm(e) {
                e.preventDefault();
                var msg = $elemInputMessage.val();
                if(msg.length < chatbx.settings.min_char) return false;
                if(sendingMessage) return false;
                sendingMessage = true;
                var tempmsgID = Math.floor(Math.random()*32767);
                var chatData = {
                    chat_id         : 'tempmsg-'+tempmsgID,
                    social_id       : chatbx.user.social_id,
                    social_name     : chatbx.user.social_name,
                    group_id        : chatbx.user.group_id,
                    gender          : chatbx.user.gender,
                    connected_with  : chatbx.user.connected_with,
                    chat_time       : Math.round(new Date().getTime() / 1000),
                    message         : msg.replace(/</g,'&lt;').replace(/>/g,'&gt;'),
                    chat_type       : 'normal'};
                addChat(chatData);
                // var chatmsg = chatData.message;
                // dmzAjax("POST", "ajax/sendMessage", {message:chatmsg}, function(data) {
                //     $(".message-"+chatData.chat_id).remove();
                //     if(data.status != 1) {
                //         error(data.text);
                //     } else {
                //         chatData.chat_id = data.lastmsgid;
                //         addChat(chatData);
                //     }
                // });
            }

            function addChat(data) {

                if($(".message-"+data.chat_id).length>0) { 
                    $(".message-"+data.chat_id).remove();
                }

                switch(data.chat_type) {
                    case 'normal': var tpl = chatbx.template.normal;
                        break;

                    case 'global': var tpl = chatbx.template.global;
                        break;

                    default: break;
                }

                for(var key in data) {
                    var re = "{"+key+"}";
                    tpl = tpl.replace(new RegExp(re, 'g'), data[key]);
                }

                $('div#chatbx_message_holder').prepend(tpl);

                if(data.social_id == chatbx.user.social_id) {
                    $('.message-'+data.msg_id+' .chatbx_message_tools').remove();
                } else {
                    if($('#chatbx_soundfx').attr("data") == 'sound-on') { 
                        chatSound();
                    }
                }

                if(chatbx.user.group_id == 0) {
                    $('.message-'+data.msg_id+' .chatbx_message_tools button.btn-danger').remove();
                }
                
            }

            function getChats(getChatsFunction) {

                console.log(activeWindow);

                if(!activeWindow) {
                    setTimeout(getChatsFunction, 1000);
                    return false;
                }

                dmzAjax("GET", "ajax/getmessage", {lastid:lastChatID}, function(data) {

                    if(data.user.social_id) {
                        updateUserInfo(data.user);
                    } else {
                        window.location = 'logout.php';
                    }

                    for(var i=0;i<data.chats.length;i++) {
                        addChat(data.chats[i]);
                    }

                    if(data.chats.length>0) {
                        lastActivity = 0;
                        lastChatID = data.chats[i-1].chat_id;
                    } else {
                        lastActivity++;
                    }

                    var nextRequest = 1000;

                    if(lastActivity > 3) {
                        nextRequest = 5000;
                    }

                    if(lastActivity > 6) {
                        nextRequest = 10000;
                    }

                    if(lastActivity > 15) {
                        nextRequest = 15000;
                    }

                    console.log(nextRequest);

                    setTimeout(getChatsFunction, nextRequest);

                });

            }

            function updateUserInfo(userinfo) {
                for(var key in userinfo) {
                    chatbx.user[key] = userinfo[key];
                }
            }

            function liveTime(liveTimeFunction) {
                $('span.time').each(function() {
                    var msgTime = $(this).attr('data-unix-time');
                    var time = Math.round(new Date().getTime() / 1000) - msgTime;
                    var day = Math.round(time / (60 * 60 * 24));
                    var week = Math.round(day / 7);
                    var remainderHour = time % (60 * 60 * 24);
                    var hour = Math.round(remainderHour / (60 * 60));
                    var remainderMinute = remainderHour % (60 * 60);
                    var minute = Math.round(remainderMinute / 60);
                    var second = remainderMinute % 60;
                    var currentTime = new Date(msgTime*1000);
                    var currentHours = ( currentTime.getHours() > 12 ) ? currentTime.getHours() - 12 : currentTime.getHours();
                    var currentHours = ( currentHours == 0 ) ? 12 : currentHours;
                    var realTime = currentHours+':'+currentTime.getMinutes();
                    var timeOfDay = ( currentTime.getHours() < 12 ) ? "AM" : "PM";
                    if(day > 7) {
                        var timeAgo = currentTime.toLocaleDateString();
                    } else if(day>=2 && day<=7) {
                        var timeAgo =  day+' days ago';
                    } else if(day==1) {
                        var timeAgo =  'Yesterday '+realTime+' '+timeOfDay;
                    } else if(hour>1) {
                        var timeAgo =  hour+' hours ago';
                    } else if(hour==1) {
                        var timeAgo =  'about an hour ago';
                    } else if(minute==1) {
                        var timeAgo =  'about a minute ago';
                    } else if(minute>1) {
                        var timeAgo =  minute+' minutes ago';
                    } else if(second>1) {
                        var timeAgo =  second+' seconds ago';
                    } else {
                        var timeAgo =  'few seconds ago';
                    }
                    $(this).html(timeAgo);
                });
                setTimeout(liveTimeFunction, 5000);
            }

            function chatSound() {
                $(".chatbx_soundfx").html('<audio src="themes/soundfx/'+chatbx.settings.soundfx+'" autoplay="true" controls preload="auto" autobuffer></audio>');
            }

            function error(text) {
                $('.chatbx_error').html(text).stop(true, true).fadeIn(200).delay(5000).fadeOut(1000);
            }

            function popupwindow(connect_to) {
                var left = (screen.width/2)-(980/2);
                var top = (screen.height/3)-(400/2);
                return window.open('connect/to/'+connect_to, 'Chatbx JhayrDmz', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=980, height=400, top='+top+', left='+left);
            }

            function dmzAjax(type, url, data, callback) {
                var req = $.ajax({
                    type: type,
                    url: url,
                    data: data,
                    dataType: "json"
                }).done(callback);
            }            

        }
    }

    $.fn.chatbx = function(method) {
        if(methods[method]) {
            return methods[method].apply(this,Array.prototype.slice.call(arguments,1));
        } else if(typeof method === "object" || !method){
            return methods.init.apply(this,arguments);
        } else {
            $.error("Method "+method+" does not exist");
        }
    };

})(jQuery);

$(document).ready(function() {
    $("#chatbx").chatbx();
});