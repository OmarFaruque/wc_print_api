<?php 
/**
 * Print product dropdown list in settings page
 * 
 *  @author Omar Faruque <ronymaha@gmail.com>
 */
?>

<select class="print-select2" multiple="multiple">
    <?php foreach ($args['options'] as $sproduct): ?>
        <option value="<?php echo esc_attr($sproduct->sku); ?>"> <?php echo esc_attr($sproduct->titleSingle); ?> </option>
    <?php endforeach; ?>    
</select>

