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

            var $elem_window = (parent == top) ? $(window.parent.window) : $(window);
            var $elemInputMessage = $("#chatbx_input_message input");
            // remove temporary
            // var active_window = true;
            var sending_message = false;
            var last_chat_id = 0, last_activity = 0;
            var chat_reply = [];

            $("#chatbx_connect_btn").live("click", function(e) {
                e.preventDefault();
                popupWindow($(this).attr("data-connect"));
            });

            dmzAjax("GET", "ajax/initialize", null, function(data) {
                if($.isEmptyObject(data) == false) {
                    chatbx = data;
                    initChatbx();
                }
            });

            function initChatbx() {
                console.log("Initialized...");

                if(!chatbx.user.social_id) return false;

                // open user list
                $("#chatbx_openlist").live('click', function() {
                    $("#chatbx_online_users").fadeIn();
                    $(".backdrop").fadeIn();
                });

                // close user list
                $(".chatbx_closelist").live('click', function() {
                    $("#chatbx_online_users").fadeOut();
                    $(".backdrop").fadeOut();
                });

                // add reply
                $("#chatbx_btn_reply").live("click", function() {
                    chat_reply.to = $(this).attr("data-name");
                    chat_reply.id = $(this).attr("data-id");
                    chat_reply.cn = $(this).attr("data-conn");
                    chat_reply.add = "$["+chat_reply.id+","+chat_reply.to+","+chat_reply.cn+"]";
                    $("#chatbx_reply span").html(chat_reply.to);
                    $("#chatbx_reply").show();
                });

                // remove reply
                $("#chatbx_reply").live("click", removeReply);

                // add emoticons
                $("#chatbx_emoticons_holder img").live("click", function() {
                    var a = $(this).attr("data-code");
                    var b = $elemInputMessage.val();
                    $elemInputMessage.val(b + a);
                    $elemInputMessage.focus();
                    $("#chatbx_emoticons_holder").hide();
                });

                // toggle emoticons
                $("#chatbx_emoticons").live('click', function() {
                    $("#chatbx_emoticons_holder").toggle();
                });

                // tools
                $(".chatbx_message").live({
                    mouseenter: function() {
                        $(".chatbx_message_tools", this).show();
                    }, mouseleave: function() {
                        $(".chatbx_message_tools", this).stop(true, true).hide();
                    }
                });

                // soundfx
                $("#chatbx_soundfx").click( function() {
                    var lem = ($(this).attr("data") == 'sound-off') ? 'sound-on' : 'sound-off';
                    if(lem == 'sound-on') {
                        $("#chatbx_soundfx i").attr('class', 'icon-volume-up');
                    } else {
                        $("#chatbx_soundfx i").attr('class', 'icon-volume-off');
                    }
                    $(this).attr("data", lem);
                });

                // submit msg
                $('#chatbx_form_composer').submit(function(e) {
                   submitForm(e);
                });

                // submit msg
                $("#chatbx_send_message").live("click", function(e) {
                    submitForm(e);
                });

                (function getChatsFunction(){
                    getChats(getChatsFunction);
                })();

                (function liveTimeFunction(){
                    liveTime(liveTimeFunction);
                })();

                // remove temporary
                // (function checkActiveWindowFunction(){
                //     checkActiveWindow(checkActiveWindowFunction);
                // })();
            }

            function submitForm(e) {
                e.preventDefault();
                var msg = $elemInputMessage.val();
                if(msg.length < chatbx.settings.min_char) return false;
                if(sending_message) return false;
                sending_message = true;
                var tempmsgID = Math.floor(Math.random()*32767);
                var chat_data = {
                    chat_id         : 'tempmsg-'+tempmsgID,
                    social_id       : chatbx.user.social_id,
                    social_name     : chatbx.user.social_name,
                    group_id        : chatbx.user.group_id,
                    group_name      : chatbx.user.group_name,
                    gender          : chatbx.user.gender,
                    connected_with  : chatbx.user.connected_with,
                    chat_time       : Math.round(new Date().getTime() / 1000),
                    message         : msg.replace(/</g,'&lt;').replace(/>/g,'&gt;'),
                    chat_type       : 'normal'};
                addChat(chat_data);
                var chatmsg = chat_data.message;
                if(chat_reply.to) {
                    var add_reply = (chat_reply.cn == "facebook")
                        ? '<a href="http://fb.com/'+chat_reply.id+'" target="_blank">To '+chat_reply.to+'</a> &#187; ' 
                        : '<a href="https://twitter.com/account/redirect_by_id?id='+chat_reply.id+'" target="_blank">To '+chat_reply.to+'</a> &#187; ';
                    chat_data.message = add_reply+chat_data.message;
                    chatmsg = chat_reply.add+" "+chatmsg;
                    addChat(chat_data);
                    removeReply();
                } else {
                    addChat(chat_data);
                }
                dmzAjax("POST", "ajax/sendMessage", {message : chatmsg}, function(data) {
                    $(".message-"+chat_data.chat_id).remove();
                    if(data.status != 1) {
                        error(data.text);
                    } else {
                        chat_data.chat_id = data.lastmsgid;
                        addChat(chat_data);
                    }
                });
                $elemInputMessage.val("");
                sending_message = false;
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

                data.message = smiley(data.message);

                for(var key in data) {
                    var re = "{"+key+"}";
                    tpl = tpl.replace(new RegExp(re, 'g'), data[key]);
                }

                $('div#chatbx_message_holder').prepend(tpl);

                if(data.social_id == chatbx.user.social_id) {
                    $('.message-'+data.chat_id+' .chatbx_message_tools').remove();
                } else {
                    if($('#chatbx_soundfx').attr("data") == 'sound-on') { 
                        chatSound();
                    }
                }

                if(chatbx.user.group_id == 0) {
                    $('.message-'+data.chat_id+' .chatbx_message_tools button.btn-danger').remove();
                }
                
            }

            function getChats(getChatsFunction) {
                dmzAjax("GET", "ajax/getMessage", {lastid : last_chat_id}, function(data) {
                    if(data.user.social_id) {
                        updateUserInfo(data.user);
                    } else {
                        window.location = "logout.php";
                    }

                    // get away u awesome loader!!!
                    if(last_chat_id == 0) getRidOfLoader();

                    for(var i=0;i<data.chats.length;i++) {
                        addChat(data.chats[i]);
                    }

                    updateUserList(data.user_list);

                    if(data.chats.length > 0) {
                        last_activity = 0;
                        last_chat_id = data.chats[i-1].chat_id;
                    } else {
                        last_activity++;
                    }

                    var next_request = 1000;

                    if(last_activity > 3) {
                        next_request = 5000;
                    }

                    if(last_activity > 6) {
                        next_request = 10000;
                    }

                    if(last_activity > 15) {
                        next_request = 15000;
                    }

                    // reduce ajax request u snobber!!!
                    // remove temporary
                    // if(!active_window && last_activity <= 15) {
                    //     next_request = 10000;
                    // }

                    console.log("Request Complete... Next: "+next_request);
                    setTimeout(getChatsFunction, next_request);
                });
            }

            function updateUserList(user_list_data) {
                $('div.chatbx_users_list').html('');
                for(var i=0;i<user_list_data.length;i++) {
                    var temp_tpl = chatbx.template.user_list;
                    for(var key in user_list_data[i]) {
                        var re = "{"+key+"}";
                        temp_tpl = temp_tpl.replace(new RegExp(re, 'g'), user_list_data[i][key]);
                    }
                    $('div.chatbx_users_list').prepend(temp_tpl);
                }
                $("#chatbx_user_list_count").html(user_list_data.length);
            }

            function updateUserInfo(userinfo) {
                for(var key in userinfo) {
                    chatbx.user[key] = userinfo[key];
                }
            }

            // remove temporary
            // function checkActiveWindow(checkActiveWindowFunction) {
            //     $elem_window.focus(function() {
            //         active_window = true;
            //     }).blur(function() {
            //         active_window = false;
            //     });
            //     console.log("Active window: "+active_window);
            //     setTimeout(checkActiveWindowFunction, 1000);
            // }

            function removeReply() {
                $("#chatbx_reply span").html('');
                $("#chatbx_reply").hide();
                chat_reply = [];
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

            function smiley(msg) {
                var exp = /(\{)+((?:[a-zA-Z-_]+))+(\})/gi;
                return msg.replace(exp,'<img src="themes/img/emotions/$2.png" alt="$2"/>');
            }

            function error(text) {
                $('.chatbx_error').html(text).stop(true, true).fadeIn(200).delay(5000).fadeOut(1000);
            }

            function popupWindow(connect_to) {
                var left = (screen.width/2)-(980/2);
                var top = (screen.height/3)-(400/2);
                return window.open('connect/to/'+connect_to, 'Chatbx JhayrDmz', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=980, height=400, top='+top+', left='+left);
            }

            function getRidOfLoader() {
                $(".backdrop").fadeOut();
                $("#chatbx_loader").fadeOut();
            }

            function dmzAjax(type, url, data, callback, fail_callback) {
                var req = $.ajax({
                    type: type,
                    url: url,
                    data: data,
                    async: true,
                    dataType: "json"
                }).done(callback).fail(fail_callback);
            }

        } // end init
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