<?php 
/**
 * Print settings default input field 
 * 
 * @author Omar Faruque <ronymaha@gmail.com>
 */

 $value = isset($options[$args['label_for']]) ? $options[$args['label_for']] : '';
 if (empty($value))
     $value = $args['default'] ? $args['default'] : $value;
 ?>
<input style="min-width: 500px;" id="<?php echo esc_attr($args['label_for']); ?>" type="<?php echo $args['type']; ?>"
value="<?php echo $value; ?>" name="print_options[<?php echo esc_attr($args['label_for']); ?>]" />
<p class="description">
<?php echo $args['desc']; ?>
</p>