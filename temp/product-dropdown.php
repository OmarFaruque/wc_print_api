<?php 
/**
 * Print product dropdown list in settings page
 * 
 *  @author Omar Faruque <ronymaha@gmail.com>
 */
?>

<?php

?>

<select style="min-width: 500px;" id="<?php echo esc_attr($args['label_for']); ?>" name="print_options[<?php echo esc_attr($args['label_for']); ?>][]" class="print-select2" multiple="multiple">
    <?php foreach ($args['options'] as $sproduct):
        if (!isset($sproduct->titleSingle))
            continue;
        ?>
        <option value="<?php echo esc_attr($sproduct->sku); ?>"> <?php echo esc_attr($sproduct->titleSingle); ?> </option>
    <?php endforeach; ?>    
</select>

