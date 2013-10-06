<div class="chatbx_message message-{chat_id} type-{group_id}">
    <div class="chatbx_message_body">
        <img class="prof pull-left" src="themes/img/avatars/{social_id}.jpg" alt/>
        <div>
            <div class="chatbx_username">
                <a href="{link}" target="_blank">{social_name}</a>
            </div>
            <div class="chatbx_rank">{group_name}</div>
            <div class="chatbx_message_text">{message}</div>
            <div class="chatbx_message_tools">
                <button id="chatbx_btn_reply" data-name="{social_name}" data-id="{social_id}" data-conn="{connected_with}" class="btn btn-success">Reply</button>
                <button id="chatbx_btn_ban" data-name="{social_name}" data-id="{social_id}" class="btn btn-danger">Ban</button>
            </div>
        </div>
    </div>
    <div class="chatbx_message_footer">
        <i class="icon-{gender}"></i> | 
        <i class="icon-time"></i> 
        <span class="time" data-unix-time="{chat_time}">few seconds ago</span> | Connected via 
        <i class="icon-{connected_with}"></i>
    </div>
</div>