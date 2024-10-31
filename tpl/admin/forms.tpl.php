<div class="wrap sendsmith-admin-container">
    <?php if (isset($this->error) && $this->error != ''): ?>
        <div class="error below-h2">
            <p><strong>ERROR</strong>: <?php echo $this->error; ?></p>	
        </div>
    <?php endif; ?>
    <h1><?php echo __('Customise your email subscription form', 'sendsmith'); ?></h1>
    <p><?php echo __('Build your email subscription form by customizing the Field Names, Attributes, Button Text, Successful Submission Message and target Interest Groups via the below settings. Simply insert the form-code [sendsmith-form] to render your email subscription form in any page or post to get the form up and running.', 'sendsmith'); ?></p>
    <?php if ($this->success && $this->success != ""): ?>
        <div id="message" class="updated notice notice-success is-dismissible below-h2">
            <p><?php echo $this->success; ?></p>
            <button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>
        </div>
    <?php endif; ?>
    <h2><?php echo __('Form Fields', 'sendsmith'); ?></h2>
    <form method="post">
        <input type="hidden" name="sendsmith_form_action" value="update_field_form" />
        <input type="hidden" name="sendsmith-doubleopt-form" value="" />
	<!--
        <p><input type="checkbox" value="1" name="sendsmith-doubleopt-form" <?php echo (isset($double_opt) && $double_opt == 1) ? "checked='checked'" : ""; ?>>Send a confirmation email to double opt in these contacts - this should always be done unless double opted in already</p>
	// -->
        <table class="widefat" id="sendsmith_admin_field_list">
            <thead>
            <th>Field name</th>
            <th>Attribute</th>
            <th>Field Type</th>
            <th>Manage</th>
            </thead>
            <tbody>
                <?php if (!empty($fields)): ?>
                    <?php foreach ($fields as $f): ?>
                        <tr class="sendsmith-input-container field-1">
                            <td class="ce_editable"><input type="text" name="field[name][]"  value="<?php echo $f['name']; ?>" /></td>
                            <td class="ce_editable">
                                <select name="field[key][]">
                                <?php if($available_fields && is_array($available_fields)){
                                                foreach($available_fields AS $field_k=>$field_v){       ?>
                                        <option <?php echo ($f['key']==$field_v['field'])?'selected="selected"':""; ?> value='<?php echo addslashes($field_v['field'])?>'><?php echo $field_v['title']?></option>
                                <?php           }
                                        }
                                ?>
			   </td>
                            <td class="ce_editable">
                                <select name="field[type][]">
                                    <option <?php echo ($f['type'] == 'text-box') ? 'selected="selected"' : ""; ?> value="text-box">Text box</option>
                                    <option <?php echo ($f['type'] == 'text-area') ? 'selected="selected"' : ""; ?> value="text-area">Text area</option>
                                </select>
                            </td>
                            <td><input type="button" class="button sendsmith-feild-delete-button" value="Delete"/></td>                
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="sendsmith-input-container field-1">
                        <td class="ce_editable"><input type="text" name="field[name][]"  value="" /></td>
                        <td class="ce_editable">
				<select name="field[key][]">
				<?php if($available_fields && is_array($available_fields)){
						foreach($available_fields AS $field_k=>$field_v){	?>
					<option value='<?php echo addslashes($field_v['field'])?>'><?php echo $field_v['title']?></option>
				<?php		}
					}
				?>
				</select>
			</td>
                        <td class="ce_editable">
                            <select name="field[type][]">
                                <option value="text-box">Text box</option>
                                <option value="text-area">Text area</option>
                            </select>
                        </td>
                        <td><input type="button" class="button sendsmith-feild-delete-button" value="Delete"/></td>                
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p><input type="button" class="button" id="sendsmith-addnew-fields" value="Add new Field" /></p>
		<p></p>
        <h2><?php echo __('Form Messages', 'sendsmith'); ?></h2>
		<p></p>
        <table>
            <tr>
                <td>Successful submission : </td><td><input type="text" name="sendsmith-form-message[success-message]" size="64" value="<?php echo(isset($form_messages) && isset($form_messages['success-message'])) ? $form_messages['success-message'] : "" ?>"/></td></td>
            </tr>
            <tr>
                <td>Empty or invalid email address format: </td><td> <input type="text" name="sendsmith-form-message[incorrect-email-message]" size="64" value="<?php echo(isset($form_messages) && isset($form_messages['incorrect-email-message'])) ? $form_messages['incorrect-email-message'] : "" ?>" /></td></td>
            </tr>
            <tr>
                <td>Email subscribers who already exist in SendSmith: </td><td> <input type="text" name="sendsmith-form-message[email-exist]" size="64" value="<?php echo(isset($form_messages) && isset($form_messages['email-exist'])) ? $form_messages['email-exist'] : "" ?>" /></td></td>
            </tr>
            <tr>
                <td>Placeholder: </td><td> <input type="text" name="sendsmith-form-message[placeholder]" size="64" value="<?php echo(isset($form_messages) && isset($form_messages['placeholder'])) ? $form_messages['placeholder'] : "" ?>" /></td></td>
            </tr>
            <tr>
                <td>Submit button text: </td><td><input type="text" name="sendsmith-form-message[submit-text]" size="64" value="<?php echo(isset($form_messages) && isset($form_messages['submit-text'])) ? $form_messages['submit-text'] : "" ?>" /></td></td>
            </tr>
        </table>
        
        <p></p>
        <p><input type="submit" value="Save" class="button" /></p>
    </form>
    <table class="widefat" id="sendsmith_admin_field_list_tpl" style="display: none;">
        <tbody>
            <tr class="sendsmith-input-container field-1">
                <td class="ce_editable"><input type="text" name="field[name][]"  value="" /></td>
                <td class="ce_editable">
			<select name="field[key][]">
				<?php if($available_fields && is_array($available_fields)){
					foreach($available_fields AS $field_k=>$field_v){       ?>
					<option value='<?php echo esc_attr($field_v['field'])?>'><?php echo $field_v['title']?></option>
                                <?php   }
                                }
                                ?>
                                </select>
		</td>
                <td class="ce_editable">
                    <select name="field[type][]">
                        <option value="text-box">Text box</option>
                        <option value="text-area">Text area</option>
                    </select>
                </td>
                <td><input type="button" class="button sendsmith-feild-delete-button" value="Delete"/></td>                
            </tr>
        </tbody>
    </table>
</div>
