<div class="wrap sendsmith-admin-container">
    <?php if (isset($this->error) && $this->error != ''): ?>
        <div class="error below-h2">
            <p><strong>ERROR</strong>: <?php echo $this->error; ?></p>	
        </div>
    <?php endif; ?>
    <h1><?php echo __('Automatically add members to your SendSmith account', 'sendsmith'); ?></h1>
    <p><?php echo __('Send onboarding sequences and welcome emails to your new members, without changing any of your sign up forms.', 'sendsmith'); ?></p>
    <p><?php echo __("Different members of your site are assigned different roles depending on your Wordpress settings. These roles are listed here, where you can also add tags to each. Nothing is added to your SendSmith account until you tick the 'Active' box. Once active any new members with that role will be passed to your SendSmith account. Different roles can now trigger different emails, so you can create a customised sequence for every new member who signs up.", 'sendsmith'); ?></p>
    <?php if ($this->success && $this->success != ""): ?>
        <div id="message" class="updated notice notice-success is-dismissible below-h2">
            <p><?php echo $this->success; ?></p>
        </div>
    <?php endif; ?>
    <form action="" method="post">
        <input type="hidden" name="sendsmith_form_action" value="update_role_tags" />
        <p><input type="checkbox" value="1" name="sendsmith-doubleopt-roles" <?php echo (isset($double_opt) && $double_opt == 1) ? "checked='checked'":""; ?>>Send a confirmation email to double opt in these contacts - your site may already be doing this</p>

        <table>
            <thead>
            <th>Roles</th>
            <th>Tags</th>
            <th>Active</th>
            </thead>
            <?php foreach ($available_roles as $role_key => $av): ?>
                <tr>
                    <td><?php echo $av; ?></td>
                    <td>
                        <select class="sendsmith-role-tags" multiple="multiple" name="sendsmith-role-tags[<?php echo $role_key; ?>][]">
                            <?php if (isset($role_tags[$role_key]) && !empty($role_tags[$role_key])): ?>
                                <?php foreach ($role_tags[$role_key] as $t): ?>
                                    <option selected="selected" value="<?php echo $t ?>"><?php echo $t ?></option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </td>
                    <td><input type="checkbox" class="sendsmith-role-tags-status" value="1" name="sendsmith-role-status[<?php echo $role_key; ?>]" <?php echo (isset($role_status[$role_key]) && $role_status[$role_key] == 1)? "checked='checked'" : "" ?>/></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <p><input type="submit" value="Save" class="button button-primary" /></p>
    </form>
</div>
