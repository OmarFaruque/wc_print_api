<?php 
/**
 * Checkbox markup
 * 
 * @author omar-faruque <ronymaha@gmail.com>
 */
?>
  <input <?php echo isset($options[$args['label_for']]) ? checked(1, $options[$args['label_for']], false) : false; ?>
    id="
    <?php echo esc_attr($args['label_for']); ?>" type="checkbox" value="1" name="print_options[
    <?php echo esc_attr($args['label_for']); ?>]" />
    <p class="description">
        <?php echo $args['desc']; ?>
    </p>