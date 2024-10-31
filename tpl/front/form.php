<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/*
 * This file will add extra fields to registration form
 */
?>
<form action="" method="post">
<?php $field_value = (!empty($_POST['sendsmith-fields']['contact']) ) ? trim($_POST['sendsmith-fields']['contact']) : ''; ?>
<?php if(isset($this->error) && $this->error != ''): ?>
    <p class="error"><?php echo $this->error; ?></p>
<?php endif; ?>
    
<?php if(isset($this->success) && $this->success != ''): ?>
    <p class="success"><?php echo $this->success; ?></p>
<?php endif; ?>
<input type="hidden" name="sendsmith_form_action" value="front_form_post" />
<input type="hidden" name="sendsmith_form_id" value="1" />
<input type="hidden" name="sendsmith_form_t" value="<?php echo esc_attr(wp_unslash($sendsmith_token)); ?>" />
<p>
    <label for="sendsmith-field-key-contact"><?php _e("Email", 'sendsmith') ?><br />
        <input type="text" name="sendsmith-fields[contact]" id="sendsmith-field-key-contact" class="input" value="<?php echo ($this->success == '') ?  esc_attr(wp_unslash($field_value)):""; ?>" placeholder="<?php echo (isset($form_messages['placeholder']))?esc_attr(wp_unslash($form_messages['placeholder'])):""; ?>" /></label>
</p>
<?php foreach ($fields as $f): ?>
    <?php $field_value = (!empty($_POST['sendsmith-fields'][$f['key']]) ) ? trim($_POST['sendsmith-fields'][$f['key']]) : ''; ?>
    <?php if ($f['type'] == 'text-box'): ?>
        <p>
            <label for="sendsmith-field-key-<?php echo $f['key']; ?>"><?php _e($f['name'], 'sendsmith') ?><br />
                <input type="text" name="sendsmith-fields[<?php echo $f['key']; ?>]" id="sendsmith-field-key-<?php echo $f['key']; ?>" class="input" value="<?php echo ($this->success == '') ? esc_attr(wp_unslash($field_value)) : ""; ?>" /></label>
        </p>
    <?php elseif ($f['type'] == 'text-area'): ?>
        <p>
            <label for="sendsmith-field-key-<?php echo $f['key']; ?>"><?php _e($f['name'], 'sendsmith') ?><br />
                <textarea  name="sendsmith-fields[<?php echo $f['key']; ?>]" id="sendsmith-field-key-<?php echo $f['key']; ?>" class="input"><?php echo ($this->success == '') ? esc_attr(wp_unslash($field_value)): ""; ?></textarea></label>
        </p>
    <?php endif; ?>
<?php endforeach; ?>
    <input type="submit" value="<?php echo (isset($form_messages['submit-text'])) ?esc_attr(wp_unslash($form_messages['submit-text'])) : "Submit"; ?>" />
</form>
