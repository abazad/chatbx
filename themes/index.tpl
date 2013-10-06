<div id="chatbx">

    <div id="chatbx_loader"><span></span></div>

    <div id="chatbx_composer" class="container-fluid">
        <div class="row-fluid">
            <form id="chatbx_form_composer" name="chatbx_form_composer">
                <a class="pull-left" href="#" target="_blank">
                    <img src="themes/img/avatars/<?=$_SESSION['social_id']?>.jpg" alt/>
                </a>
                <div id="chatbx_input_message">
                    <input class="input-block-level pull-left" type="text" name="" placeholder="Type your message here"/>
                    <div class="chatbx_error">Test</div>
                </div>
                <button id="chatbx_send_message" class="btn pull-right" type="button" name="send_message" placeholder="">
                    <i class="icon-comment"></i>
                </button>
                <button id="chatbx_emoticons" class="btn pull-right" data-toggle="dropdown" type="button" name="" placeholder="">
                    <i class="icon-smile"></i>
                </button>
                <div id="chatbx_emoticons_holder" role="menu" aria-labelledby="dLabel">
                    <?php foreach (glob("themes/img/emotions/*.png") as $filename) {
                            echo '<img src="'.$filename.'" data-code="{'.basename($filename, ".png").'}"/>';
                        } ?>
                </div>
                <a id="chatbx_reply" class="btn btn-success">
                    <i class="icon-remove"></i> To: 
                    <span data-name="" data-id=""></span>
                </a>
            </form>
        </div>
    </div>

    <div id="chatbx_container" class="container-fluid">
        <div class="row-fluid">
            <div id="chatbx_message_holder">
                <!-- chats goes here baby! -->
            </div>
        </div>
    </div>

    <div id="chatbx_footer" class="container-fluid">
        <div class="row-fluid">
            <div id="chatbx_tools">
                <button id="chatbx_openlist" class="btn btn-primary pull-left" href=""><i class="icon-user"></i> <span id="chatbx_user_list_count">0</span> Online</button>
                <button id="chatbx_soundfx" class="btn" data="sound-on"><i class="icon-volume-up"></i> Sound</button>
                <a class="btn btn-danger" href="logout.php"><i class="icon-off"></i> Sign Out</a>
                <!-- <button class="btn btn-primary" type="submit"><i class="icon-wrench"></i> Settings</button> -->
            </div>
        </div>
    </div>

    <!-- USER ONLINE -->
    <div id="chatbx_online_users">
        <div>
            <div class="header">
                <button type="button" class="chatbx_closelist pull-right">x</button>
                <h3>User Online</h3>
            </div>
            <div class="body">
                <div class="chatbx_users_list">
                    <div>
                        <img class="pull-left" src="themes/img/avatars/993656394.jpg"/>
                        <div class="pull-right"><i class="icon-facebook"></i></div>
                        <div>Jhayr Dmz</div>
                    </div>
                    <div>
                        <img class="pull-left" src="themes/img/avatars/993656394.jpg"/>
                        <div class="pull-right"><i class="icon-twitter"></i></div>
                        <div>Jhayr Dmz</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END USER ONLINE -->

</div>
<div class="chatbx_soundfx" style="display:none"></div>
<div class="backdrop"></div>