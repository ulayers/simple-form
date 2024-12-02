<?php

/**
 * Plugin Name:          Simple Form
 * Plugin URI:           https://ulayers.com
 * Description:          A simple form plugin that allows users to submit their name and phone number. Data is saved to a custom table and can be searched via a shortcode.
 * Version:              1.0.0
 * Author:               Mohamed Ali
 * Author URI:           https://ulayers.com
 * License:              GPL-2.0+
 * License URI:          http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (! defined('ABSPATH')) {
    exit;
}


function sf_create_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'simple_form';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        phone varchar(15) NOT NULL,
        time datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'sf_create_table');


function sf_form_shortcode()
{
    ob_start();
?>
    <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
        <p>
            Name (required) <br>
            <input type="text" name="sf-name" value="" required>
        </p>
        <p>
            Phone Number (required) <br>
            <input type="tel" name="sf-phone" value="" required>
        </p>
        <p>
            <input type="submit" name="sf-submitted" value="Send">
        </p>
    </form>
<?php
    return ob_get_clean();
}

add_shortcode('simple_form', 'sf_form_shortcode');


function sf_handle_form_submission()
{
    global $wpdb;
    if (isset($_POST['sf-submitted'])) {
        $name   = sanitize_text_field($_POST['sf-name']);
        $phone  = sanitize_text_field($_POST['sf-phone']);
        $table_name = $wpdb->prefix . 'simple_form';

        $wpdb->insert(
            $table_name,
            [
                'name' => $name,
                'phone' => $phone
            ]
        );
        echo "<p>Thanks for your submission!</p>";
    }
}
add_action('init', 'sf_handle_form_submission');

function sf_search_shortcode()
{
    ob_start();
?>
    <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
        <p>
            Search by Phone Number: <br>
            <input type="text" name="sf-search" pattern="[0-9]+" value="" required>
            <input type="submit" name="sf-search-submitted" value="Search">
        </p>
    </form>
<?php

    if (isset($_POST['sf-search-submitted'])) {
        global $wpdb;
        $phone = sanitize_text_field($_POST['sf-search']);
        $table_name = $wpdb->prefix . 'simple_form';

        $results = $wpdb->get_results($wpdb->prepare("SELECT id, name, phone, time FROM $table_name WHERE phone = %s", $phone));

        if ($results) {
            foreach ($results as $row) {
                echo '<table>';
                echo "<tr><th>ID</th><td>{$row->id}</td></tr>";
                echo "<tr><th>Name</th><td>{$row->name}</td></tr>";
                echo "<tr><th>Phone</th><td>{$row->phone}</td></tr>";
                echo "<tr><th>Time</th><td>{$row->time}</td></tr>";
                echo '</table>';
            }
        } else {
            echo '<p>No results found.</p>';
        }
    }

    return ob_get_clean();
}

add_shortcode('simple_form_search', 'sf_search_shortcode');
