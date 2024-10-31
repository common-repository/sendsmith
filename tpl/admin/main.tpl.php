<div class="wrap sendsmith-admin-container">
    <?php if (isset($this->error) && $this->error != ''): ?>
        <div class="error below-h2">
            <p><strong>ERROR</strong>: <?php echo $this->error; ?></p>
        </div>
    <?php endif; ?>
    <br/><img src='<?php echo SENDSMITH_ASSETS_URL.'images/logo_icon.png';?>' width="150" />
        <h2>Welcome to SendSmith! Connect your WordPress website to SendSmith in seconds.</h2>
        <p>SendSmith is a 2nd generation email marketing platform launched in 2016. It is simple to use and offers a number of free responsive email templates that can be customised using drag-and-drop means. The post-campaign reporting is currently the most informative on the market and one of the most striking features is the tracking of user behaviour post-click from the email campaign.</p>
        <p>Free Registration and 1,000 free email sends every month and available in 11 languages.</p>
        <p>Please do not hesitate to email us at <a href="mailto:info@sendsmith.com">info@sendsmith.com</a> if you need help.</p>
        <h2>Form Setup - A simple way to build your email subscription form.</h2>
        <p>You can build your email subscription form with this SendSmith plugin and the subscribers will be automatically added to your SendSmith account, please follow the below procedure to build your email subscription form on your website now:</p>
        <ol>
                <li>Sign up for a SendSmith account <a href="<?php echo SENDSMITH_URL.'/register/';?>" id="sign-up" target="_blank">http://www.sendsmith.com/</a></li>
                <li>Login to the SendSmith account and then go to Settings</li>
                <li>Scroll down to "API Key(s) For Join Mailing List From Your Website(s)" section</li>
                <li>Click "Create Identity" to generate your API Key</li>
                <li>Copy and paste the generated API Key and API Secret to your WordPress Admin Panel > SendSmith Plugin > Settings</li>
        </ol>
        <p>Almost there! Now you can simply insert the form-code [sendsmith-form] or press the button "SendSmith Form" from the text editor to render your email subscription form in any page or post to get the form up and running. All the email addresses that you capture from your WordPress website will automatically be added to your SendSmith account.</p>
        <p>Try now!</p>
</div>