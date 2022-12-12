<?php 
/**
 * Settings html markup
 *  
 * @author Omar Faruque <ronymaha@gmail.com>
 */
?>

<div class="wrap">
    <h1>
        <?php echo esc_html(get_admin_page_title()); ?>
    </h1>
    <form action="options.php" method="post">
        <?php
            // output security fields for the registered setting "wporg"
            settings_fields('print-settings');
            // output setting sections and their fields
            // (sections are registered for "wporg", each field is registered to a specific section)
            do_settings_sections('print-settings');

            // output save settings button
            submit_button('Save Settings');
                    ?>
    </form>
</div>