<?php if (!current_user_can('manage_options')) wp_die('You do not have sufficient permissions to access this page') ?>

<div class="wrap">
    <div id="poststuff"><div id="post-body">

    <div class="premium_plugin_notice">
        <h3 style="margin:5px;"><a href="https://wp-ecommerce.net/wordpress-coming-soon-plugin" target="_blank">Premium Version: WordPress Site offline/Coming Soon Plus Plugin</a></h3>
    </div>
    <div id="icon-options-general" class="icon32"><br /></div>

    <h2><?php _e('Site Offline Plugin Options'); ?></h2>
    <form method="POST" action="">

        <div class="postbox">
	<h3><label for="title">Activate/Deactivate Site Offline Mode</label></h3>
	<div class="inside">
            <p>Enabled: <label><input type="radio" value="true" name="cp_siteoffline_enabled" <?php echo ($options['enabled'] === true ? 'checked' : '' ); ?> />Yes</label> <label><input type="radio" value="false" name="cp_siteoffline_enabled" <?php echo ($options['enabled'] === false ? 'checked' : '' ); ?> /> No</label></p>
        
            <input type="submit" name="cpso_save_settings" value="<?php _e('Save Changes'); ?>" class="button button-primary"/>
        </div></div><!-- end of .inside and .postbox -->

        <div class="postbox">
	<h3><label for="title">Site Offline Message/Content</label></h3>
	<div class="inside">
        
            <p><textarea spellcheck='false' rows='22' name="cp_siteoffline_content">
                <?php
                    if ($options['content'] === NULL || empty($options['content'])){
                        include('content.htm');
                    }
                    else{
                        echo $options['content'];
                    }
                ?>
                </textarea>
            </p>

        </div></div><!-- end of .poststuff -->
        <input type="submit" name="cpso_save_settings" value="<?php _e('Save Changes'); ?>" class="button button-primary"/>
    </form>
    
    </div></div><!-- end of .poststuff and post-body -->
</div><!-- end of .wrap -->
<style>
textarea[name='cp_siteoffline_content']
{
font-family:Monaco,Consolas,monospace;
width:100%;
}
</style>