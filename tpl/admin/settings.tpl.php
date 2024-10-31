<div class="wrap sendsmith-admin-container">
    <?php if (isset($this->error) && $this->error != ''): ?>
        <div class="error below-h2">
            <p><strong>ERROR</strong>: <?php echo $this->error; ?></p>	
        </div>
    <?php endif; ?>
    <h1><?php echo __('Connect your SendSmith account to your WordPress website now', 'sendsmith'); ?></h1>
    <p><?php echo __('Login to your SendSmith account and generate the API Key(s) under Settings > API Key(s) For Join Mailing List From Your Website(s).', 'sendsmith'); ?></p>
    <ol>
        <li><?php echo __('Click "Create Identity"', 'sendsmith'); ?></li>
        <li><?php echo __('Enter Identity Name', 'sendsmith'); ?></li>
        <li><?php echo __('Select the Interest Group(s) that you wish to add the form subscribers to for this Identity', 'sendsmith'); ?></li>
        <li><?php echo __('Click Submit', 'sendsmith'); ?></li>
        <li><?php echo __('Copy and paste the generated API Key and API Secret there:-', 'sendsmith'); ?></li>
    </ol>
    <?php if($this->success && $this->success != ""): ?>
    <div id="message" class="updated notice notice-success is-dismissible below-h2">
        <p><?php echo $this->success; ?></p>
    </div>
    <?php endif; ?>
    <form action="" method="post">
        <input type="hidden" name="sendsmith_form_action" value="update_sendsmith_settings" />
	<?php $nonce = wp_create_nonce( 'sendsmith-nonce' ); ?>
	<input type="hidden" name="sendsmith-nonce" value="<?php echo $nonce;?>" />
        <p><label for="sendsmith-setting-apikey"><strong>API Key</strong></label></p>
        <p><input class="sendsmith-api-key" type="text" name="sendsmith-api-key" value="<?php echo $this->api_key; ?>"/></p>

        <p><label for="sendsmith-setting-secret"><strong>API Secret</strong></label></p>
        <p><input class="sendsmith-api-secret" type="text" name="sendsmith-api-secret" value="<?php echo $this->api_secret; ?>" /></p>

        <p><input type="submit"  class="button" value="Save"/></p>
    </form>
	<p><br/></p>
</div>
