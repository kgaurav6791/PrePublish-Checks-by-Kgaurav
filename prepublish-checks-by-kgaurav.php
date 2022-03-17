<?php

/**
 * Plugin Name: PrePublish Checks by Kgaurav
 * Plugin URI: https://github.com/kgaurav6791/PrePublish-Checks-by-Kgaurav
 * Description: A simple plugin to enforce variety of checks before publishing any post.Define minimum and maximum title length.Make presence of a featured image compulsory.Specify the minimum height and width for featured images.Bonus feature check for post slug to be in english.
 * Version: 1.0.0
 * Author: Kgaurav
 * Author URI: https://github.com/kgaurav6791
 **/
if (!defined('ABSPATH')) {
    die('WHAT ARE YOU DOING,YOU SILLY HUMAN!!!!!');
}

//For assigning default values for our setting options
const prepublish_checks_by_kgaurav_titlemindefault            =   10;
const prepublish_checks_by_kgaurav_titlemaxdefault            =   280;
const prepublish_checks_by_kgaurav_slugenglishcheck           =   1;    // 1 means yes slug should be in english,0 means no don't check for slug to be english
const prepublish_checks_by_kgaurav_featuredimagecheck         =   1;    // 1 means yes
const prepublish_checks_by_kgaurav_featuredimagewidthdefault  =   500;
const prepublish_checks_by_kgaurav_featuredimageheightdefault =   400;
const prepublish_checks_by_kgaurav_featuredimagewidthmaxdefault  =   4000;
const prepublish_checks_by_kgaurav_featuredimageheightmaxdefault =   4000;

/**
 * Fire a callback only when posts are transitioned to 'publish'.
 *
 * @param string  $new_status New post status.
 * @param string  $old_status Old post status.
 * @param WP_Post $post       Post object.
 */
function prepublish_checks_by_kgaurav($new_status, $old_status, $post)
{
    if (('publish' === $new_status && 'publish' !== $old_status)
        && 'post' === $post->post_type
    ) {
        // do stuff
        $post_id = get_the_ID();
        prevent_post_publishing($post_id, $post);
    }
}
add_action('transition_post_status', 'prepublish_checks_by_kgaurav', -1, 3);

//similar to 
//add_action('save_post', 'prevent_post_publishing', -1, 3);
function prevent_post_publishing($post_id, $post)
{

    $message = "";

    $prepublish_checks_by_kgaurav_settings_data =   get_option('prepublish_checks_by_kgaurav_settings_data');

    // You also add a post type verification here,
    // like $post->post_type == 'your_custom_post_type'
    if ($prepublish_checks_by_kgaurav_settings_data['featuredimagecheck'] == 1) {
        if (!has_post_thumbnail($post_id)) {
            $message = '<p>Please, add a Featured Image!</p>';
        }
    }
    $image_id = get_post_thumbnail_id($post->ID);
    $image_meta = wp_get_attachment_image_src($image_id, 'full');
    $width = $image_meta[1];
    $height = $image_meta[2];
    if (has_post_thumbnail($post_id) && $prepublish_checks_by_kgaurav_settings_data['featuredimagecheck'] == 1) {
        if (!($width >= $prepublish_checks_by_kgaurav_settings_data['featuredimagewidth'] && $height >=  $prepublish_checks_by_kgaurav_settings_data['featuredimageheight'])) {
            $message = $message . '<p>Featured Image too small! Image should be atleast ' . $prepublish_checks_by_kgaurav_settings_data['featuredimagewidth'] . ' x ' . $prepublish_checks_by_kgaurav_settings_data['featuredimageheight'] . ' .<br />';
            $message = $message . '(Your Present Image Size is <b>' . $width . ' x ' . $height . '</b>).</p>';
        }
        if (!($width <= $prepublish_checks_by_kgaurav_settings_data['featuredimagewidthmax'] && $height <=  $prepublish_checks_by_kgaurav_settings_data['featuredimageheightmax'])) {
            $message = $message . '<p>Featured Image too big! Image should be atmost ' . $prepublish_checks_by_kgaurav_settings_data['featuredimagewidthmax'] . ' x ' . $prepublish_checks_by_kgaurav_settings_data['featuredimageheightmax'] . ' .<br />';
            $message = $message . '(Your Present Image Size is <b>' . $width . ' x ' . $height . '</b>).</p>';
        }
    }
    $title = get_the_title($post);


    if (strlen($title) > $prepublish_checks_by_kgaurav_settings_data['titlemax']) {
        $message = $message . '<p>Title Length is too big .Make sure it is less than ' . $prepublish_checks_by_kgaurav_settings_data['titlemax'] . ' characters.<br>';
        $message = $message . '(Your current title is <b>' . strlen($title) . '</b> characters long).</p>';
    }
    if (strlen($title) < $prepublish_checks_by_kgaurav_settings_data['titlemin']) {
        $message = $message . '<p>Title length is too small .Make sure it is more than ' . $prepublish_checks_by_kgaurav_settings_data['titlemin'] . ' characters.<br>';
        $message = $message . '(Your current title is <b>' . strlen($title) . '</b> characters long).</p>';
    }
    if ($prepublish_checks_by_kgaurav_settings_data['slugenglishcheck'] == 1) {
        $post_slug = $post->post_name;
        if (!(preg_match('/^[A-Za-z]+/',  $post_slug))) // ^ OUTSIDE [] MEANS START-OF-STRING  , ^ INSIDE [] MEANS NOT( LIKE !)  ,    + MEANS ONE OR MORE CHARACTERS(OF PRECEEDING)
        {
            $message = $message . '<p>Slug (URL) is not in English .</p>';
        }
    }
    $editurl = '<p><a href="' . admin_url('post.php?post=' . $post_id . '&action=edit') . '">Go back and edit the post</a></p>';
    if ($message != "" && $post->post_status == 'publish') {
        $message = $message . $editurl;
        $post->post_status = 'draft';
        wp_update_post($post);
        wp_die($message, 'Prepublishing checks failed');
    }
}

register_deactivation_hook(__FILE__, 'prepublish_by_kgaurav_deactivate');
function prepublish_by_kgaurav_deactivate()
{

    $option_name = 'prepublish_checks_by_kgaurav_settings_data';
    delete_option($option_name);
}

add_action('admin_init', 'prepublish_by_kgaurav_options_register');
add_action('admin_menu', 'prepublish_by_kgaurav_add_page');
function prepublish_by_kgaurav_options_register()
{


    $prepublish_checks_by_kgaurav_settings_data = array(
        'titlemin'               =>   prepublish_checks_by_kgaurav_titlemindefault,
        'titlemax'               =>   prepublish_checks_by_kgaurav_titlemaxdefault,
        'slugenglishcheck'       =>   prepublish_checks_by_kgaurav_slugenglishcheck,    // 1 means yes slug should be in english,0 means no don't check for slug to be english
        'featuredimagecheck'     =>   prepublish_checks_by_kgaurav_featuredimagecheck,    // 1 means yes
        'featuredimagewidth'     =>   prepublish_checks_by_kgaurav_featuredimagewidthdefault,
        'featuredimageheight'    =>   prepublish_checks_by_kgaurav_featuredimageheightdefault,
        'featuredimageheightmax' =>   prepublish_checks_by_kgaurav_featuredimageheightmaxdefault,
        'featuredimagewidthmax' =>    prepublish_checks_by_kgaurav_featuredimagewidthmaxdefault,
    );

    if (get_option('prepublish_checks_by_kgaurav_settings_data') === FALSE) {
        add_option('prepublish_checks_by_kgaurav_settings_data',  $prepublish_checks_by_kgaurav_settings_data);
    }


    $prepublish_checks_by_kgaurav_settings_data =   get_option('prepublish_checks_by_kgaurav_settings_data');

    register_setting('prepublish_by_kgaurav_option_group', 'prepublish_checks_by_kgaurav_settings_data', 'prepublish_by_kgaurav_callback_validate');
}

//validate settings data.Sanitize and validate input. Accepts an array, return a sanitized array.
function prepublish_by_kgaurav_callback_validate($input)
{

    $input['titlemin'] = absint($input['titlemin']);
    if ($input['titlemin'] < 1 || $input['titlemin'] > 20000) {
        $input['titlemin'] = prepublish_checks_by_kgaurav_titlemindefault;
    }


    $input['titlemax'] =  absint($input['titlemax']);
    if ($input['titlemax'] < 1 || $input['titlemax'] > 20000) {
        $input['titlemax'] = prepublish_checks_by_kgaurav_titlemaxdefault;
    }


    if ($input['titlemax'] <=  $input['titlemin']) {
        $input['titlemax'] = prepublish_checks_by_kgaurav_titlemaxdefault;
        $input['titlemin'] = prepublish_checks_by_kgaurav_titlemindefault;
    }


    $input['featuredimagecheck'] = ($input['featuredimagecheck'] == 2 ? 2 : 1);


    $input['slugenglishcheck'] =  ($input['slugenglishcheck'] == 2 ? 2 : 1);


    $input['featuredimagewidth'] = absint($input['featuredimagewidth']);
    if ($input['featuredimagewidth'] < 1 || $input['featuredimagewidth'] > 20000) {
        $input['featuredimagewidth'] = prepublish_checks_by_kgaurav_featuredimagewidthdefault;
    }


    $input['featuredimageheight'] =  absint($input['featuredimageheight']);
    if ($input['featuredimageheight'] < 1 || $input['featuredimageheight'] > 20000) {
        $input['featuredimageheight'] = prepublish_checks_by_kgaurav_featuredimageheightdefault;
    }


    $input['featuredimagewidthmax'] = absint($input['featuredimagewidthmax']);
    if ($input['featuredimagewidthmax'] < 1 || $input['featuredimagewidthmax'] > 20000) {
        $input['featuredimagewidthmax'] = prepublish_checks_by_kgaurav_featuredimagewidthmaxdefault;
    }


    $input['featuredimageheightmax'] =  absint($input['featuredimageheightmax']);
    if ($input['featuredimageheightmax'] < 1 || $input['featuredimageheightmax'] > 20000) {
        $input['featuredimageheightmax'] = prepublish_checks_by_kgaurav_featuredimageheightmaxdefault;
    }


    if ($input['featuredimagewidthmax'] <=  $input['featuredimagewidth']) {
        $input['featuredimagewidthmax'] = $featuredimagewidthmaxdefault;
        $input['featuredimagewidth'] = prepublish_checks_by_kgaurav_featuredimagewidthdefault;
    }


    if ($input['featuredimageheightmax'] <=  $input['featuredimageheight']) {
        $input['featuredimageheightmax'] = $featuredimageheightmaxdefault;
        $input['featuredimageheight'] = prepublish_checks_by_kgaurav_featuredimageheightdefault;
    }
    return $input;
}

//Add settings page
function prepublish_by_kgaurav_add_settings_link($links)
{
    $settings_link = '<a href="options-general.php?page=prepublish_by_kgaurav_plugin">' . __('Settings') . '</a>';
    array_push($links, $settings_link);
    return $links;
}
$pluginname = plugin_basename(__FILE__);
add_filter("plugin_action_links_$pluginname", 'prepublish_by_kgaurav_add_settings_link');


// Add menu page
function prepublish_by_kgaurav_add_page()
{
    add_options_page('Prepublish Checks Settings', 'Prepublish Checks', 'manage_options', 'prepublish_by_kgaurav_plugin', 'prepublish_by_kgaurav_options_page_callback');

    /*add_options_page SYNTAX

    ('Page Title', 
    'Plugin Menu'-adds a link under the settings menu called “Plugin Menu”.', 
    'manage_options',-You must have the “manage_options” capability to get there though (admins only).
     'myplugin', -The link this will be will in fact be /wp-admin/options-general.php?page=plugin (so “plugin” needs to be something only you will use).
     'myplugin_options_page_callback_function');

     */
}
function prepublish_by_kgaurav_options_page_callback()
{

?>
    <div>
        <?php screen_icon(); ?>
        <h1>Settings For PrePublish Checks by Kgaurav</h1>
        <form method="post" action="options.php">
            <?php settings_fields('prepublish_by_kgaurav_option_group'); //Output nonce, action, and option_page fields for a settings page FOR this particular option group
            $prepublish_checks_by_kgaurav_settings_data =   get_option('prepublish_checks_by_kgaurav_settings_data');

            if (empty($prepublish_checks_by_kgaurav_settings_data['titlemin'])) {
                $prepublish_checks_by_kgaurav_settings_data['titlemin'] = prepublish_checks_by_kgaurav_titlemindefault;
            }
            if (empty($prepublish_checks_by_kgaurav_settings_data['titlemax'])) {
                $prepublish_checks_by_kgaurav_settings_data['titlemax'] = prepublish_checks_by_kgaurav_titlemaxdefault;
            }
            if (empty($prepublish_checks_by_kgaurav_settings_data['featuredimageheight'])) {
                $prepublish_checks_by_kgaurav_settings_data['featuredimageheight'] = prepublish_checks_by_kgaurav_featuredimageheightdefault;
            }
            if (empty($prepublish_checks_by_kgaurav_settings_data['featuredimagewidth'])) {
                $prepublish_checks_by_kgaurav_settings_data['featuredimagewidth'] = prepublish_checks_by_kgaurav_featuredimagewidthdefault;
            }
            if (empty($prepublish_checks_by_kgaurav_settings_data['featuredimageheightmax'])) {
                $prepublish_checks_by_kgaurav_settings_data['featuredimageheightmax'] = prepublish_checks_by_kgaurav_featuredimageheightmaxdefault;
            }
            if (empty($prepublish_checks_by_kgaurav_settings_data['featuredimagewidthmax'])) {
                $prepublish_checks_by_kgaurav_settings_data['featuredimagewidthmax'] = prepublish_checks_by_kgaurav_featuredimagewidthmaxdefault;
            }
            if (empty($prepublish_checks_by_kgaurav_settings_data['slugenglishcheck'])) {
                $prepublish_checks_by_kgaurav_settings_data['slugenglishcheck'] = prepublish_checks_by_kgaurav_slugenglishcheck;
            }
            if (empty($prepublish_checks_by_kgaurav_settings_data['featuredimagecheck'])) {
                $prepublish_checks_by_kgaurav_settings_data['featuredimagecheck'] = prepublish_checks_by_kgaurav_featuredimagecheck;
            }


            ?>
            <h4>NOTE-DEACTIVATING WILL RESET ALL THE VALUES TO DEFAULT!!</h4>
            <table>
                <tr style="background:rgba(255, 99, 71, 0.5);">
                    <th scope="row"><label>Title Minimum Length(default 10):</label></th>
                    <td><input type="text" name="prepublish_checks_by_kgaurav_settings_data[titlemin]" value="<?php echo $prepublish_checks_by_kgaurav_settings_data['titlemin']; ?>" /></td>
                </tr>
                <tr style="background:#bebebe;">
                    <th scope="row"><label>Title Max Length(default 280):</label></th>
                    <td><input type="text" name="prepublish_checks_by_kgaurav_settings_data[titlemax]" value="<?php echo $prepublish_checks_by_kgaurav_settings_data['titlemax']; ?>" /></td>
                </tr>
                <tr style="background:rgba(255, 99, 71, 0.5);">
                    <th scope="row"><label>Check to see if slug is in English(default yes):</label></th>
                    <td>
                        <select name="prepublish_checks_by_kgaurav_settings_data[slugenglishcheck]">
                            <option value="1" <?php selected($prepublish_checks_by_kgaurav_settings_data['slugenglishcheck'], 1); ?>>YES</option>
                            <option value="2" <?php selected($prepublish_checks_by_kgaurav_settings_data['slugenglishcheck'], 2); ?>>NO</option>
                        </select>
                    </td>
                </tr>
                <tr style="background:#bebebe;">
                    <th scope="row"><label>Make Featured Image Compulsory(default yes):</label></th>
                    <td>
                        <select name="prepublish_checks_by_kgaurav_settings_data[featuredimagecheck]">
                            <option value="1" <?php selected($prepublish_checks_by_kgaurav_settings_data['featuredimagecheck'], 1); ?>>YES</option>
                            <option value="2" <?php selected($prepublish_checks_by_kgaurav_settings_data['featuredimagecheck'], 2); ?>>NO</option>
                        </select>
                    </td>
                </tr>
                <tr style="background:rgba(255, 99, 71, 0.5);">
                    <th scope="row"><label>Minimum Featured Image Width(default 500)<br />(only works if 'Make Featured Image Compulsory option' above is selected).</label></th>
                    <td><input type="text" name="prepublish_checks_by_kgaurav_settings_data[featuredimagewidth]" value="<?php echo $prepublish_checks_by_kgaurav_settings_data['featuredimagewidth']; ?>" /></td>
                </tr>
                <tr style="background:#bebebe;">
                    <th scope="row"><label>Minimum Featured Image Height(default 400)<br />(only works if 'Make Featured Image Compulsory option' above is selected).</label></th>
                    <td><input type="text" name="prepublish_checks_by_kgaurav_settings_data[featuredimageheight]" value="<?php echo $prepublish_checks_by_kgaurav_settings_data['featuredimageheight']; ?>" /></td>
                </tr>
                <tr style="background:rgba(255, 99, 71, 0.5);">
                    <th scope="row"><label>Maximum Featured Image Width(default 4000)<br />(only works if 'Make Featured Image Compulsory option' above is selected).</label></th>
                    <td><input type="text" name="prepublish_checks_by_kgaurav_settings_data[featuredimagewidthmax]" value="<?php echo $prepublish_checks_by_kgaurav_settings_data['featuredimagewidthmax']; ?>" /></td>
                </tr>
                <tr style="background:#bebebe;">
                    <th scope="row"><label>Maximum Featured Image Height(default 4000)<br />(only works if 'Make Featured Image Compulsory option' above is selected).</label></th>
                    <td><input type="text" name="prepublish_checks_by_kgaurav_settings_data[featuredimageheightmax]" value="<?php echo $prepublish_checks_by_kgaurav_settings_data['featuredimageheightmax']; ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
<?php
} ?>