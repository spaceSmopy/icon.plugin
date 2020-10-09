<?php
/*
Plugin Name: Icon Plugin
Plugin URI: http://github.com
Description: Icon Plugin in OOP
Author: Smopy
Author URI: http://github.com/spaceSmopy
Version: 1.0.0
*/



class postTitleIcon {

    public $dashIcons = array('dashicons-buddicons-activity', 'dashicons-buddicons-bbpress-logo', 'dashicons-buddicons-buddypress-logo', 'dashicons-buddicons-community', 'dashicons-buddicons-forums', 'dashicons-buddicons-friends', 'dashicons-buddicons-groups', 'dashicons-buddicons-pm', 'dashicons-buddicons-replies', 'dashicons-buddicons-topics', 'dashicons-buddicons-tracking');
    public function __construct() {
        add_filter( 'the_title', array( $this, 'form' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
        add_filter( 'admin_menu', array( $this, 'iconPlugin_register_options_page' ) );
    }

    public function iconPlugin_register_options_page() {
        add_options_page('Post icon', 'Post icon', 'manage_options', 'iconplugin', array( $this, 'iconplugin_options_page' ));
        register_setting( 'iconplugin_options_group', 'selected_postTypes', 'iconplugin_callback' );
        register_setting( 'iconplugin_options_group', 'dashIcon', 'iconplugin_callback' );
        register_setting( 'iconplugin_options_group', 'positionIcon', 'iconplugin_callback' );
        register_setting( 'iconplugin_options_group', 'plugin_status', 'iconplugin_callback' );
    }

    public function assets() {

        wp_register_style( 'plugin_customCss', plugin_dir_url( __FILE__ ) . 'assets/plugin_customCss.css', false, '1.0.0' );

        wp_enqueue_style( 'plugin_customCss' );
    }

//  Private variables that User Select
    public function get_selected_postTypes() {
        //        Set default value
        $selected_postTypes = get_option( 'selected_postTypes' );
        if ( empty($selected_postTypes)){
            $postArr = $this->get_clear_post_types();
            $firstEl = array_shift($postArr);
            update_option( 'selected_postTypes', array($firstEl));
            return get_option('selected_postTypes');
        }
        return get_option('selected_postTypes');
    }
    private function get_dashIcon() {
        //        Set default value
        $dashIcon = get_option( 'dashIcon' );
        if ( empty($dashIcon)){
            update_option('dashIcon', $this->dashIcons[0]);
            return get_option('dashIcon');
        }
        return get_option('dashIcon');
    }
    private function get_positionIcon() {
        //        Set default value
        $positionIcon = get_option( 'positionIcon' );
        if ( empty($positionIcon)){
            update_option('positionIcon', 'positionIconLeft');
            return get_option('positionIcon');
        } else {
            return get_option('positionIcon');
        }

    }
    private function get_pluginStatus() {
        //        Set default value
        $plugin_status = get_option( 'plugin_status' );
        if ( empty($plugin_status)){
            update_option( 'plugin_status', 'active');
            return get_option('plugin_status');
        } else {
            return get_option('plugin_status');
        }

    }

    public function iconplugin_options_page(){
        /**
         * HTML of Option Page
         */
        ?>
        <div>
            <h2>Icon Plugin Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields( 'iconplugin_options_group' ); ?>
                <?php
//              Needed variables
                $post_types = $this->get_clear_post_types();
                $selected_postTypes = $this->get_selected_postTypes();
                $dashIcon = $this->get_dashIcon();
                $positionIcon = $this->get_positionIcon();
                $pluginStatus = $this->get_pluginStatus();

                ?>
                <label for="plugin_status">Active / Deactivate</label>
                <input type="radio" <?php echo checked($pluginStatus, 'active')?> name="plugin_status"  id="active" value="active">
                <input type="radio" <?php echo checked($pluginStatus, 'deactive')?> name="plugin_status"  id="deactive" value="deactive">

                <?php if($pluginStatus == 'active'){?>
                    <h2>Choose Post Types where icon will appear:</h2>
                    <div>
                        <?php
                        foreach ( $post_types as $post_type ) {
                            if (in_array($post_type, $selected_postTypes)){
                                $checked = 'checked';
                            } else {
                                $checked = '';
                            }
                            ?>
                            <input type="checkbox" <?php echo $checked;?> name="selected_postTypes[]" id="<?php echo $post_type?>" value="<?php echo $post_type?>">
                            <label for="<?php echo $post_type?>"><?php echo $post_type?></label><br>
                        <?php } ?>
                    </div>

                    <h2>Choose your icon:</h2>
                    <div id="icons">
                        <?php foreach ($this->dashIcons as $dashIconName){
                            ?>
                            <div class="icon_block">
                                <input type="radio" <?php echo checked($dashIcon, $dashIconName)?> name="dashIcon"  id="<?php echo $dashIconName?>" value="<?php echo $dashIconName?>">
                                <label for="<?php echo $dashIconName?>"><span class="<?php echo $dashIconName; ?> span_icons" ></span></label>
                            </div>
                        <?php } ?>
                    </div>

                    <h2>Choose icon position:</h2>
                    <div id="icon_position">
                        <input type="radio" <?php echo checked($positionIcon, 'positionIconLeft')?> name="positionIcon"  id="positionIconLeft" value="positionIconLeft">
                        <label for="positionIconLeft">Icon on Left</label>
                        <input type="radio" <?php echo checked($positionIcon, 'positionIconRight')?> name="positionIcon"  id="positionIconRight" value="positionIconRight">
                        <label for="positionIconRight">Icon on Right</label>
                    </div>
                <?php }?>



                <?php  submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function get_clear_post_types() {
        /**
         * Return Array of used Post Types
         */
        $args = array(
            'public'   => true,
        );
        $output   = 'names'; // names or objects, note names is the default
        $operator = 'and';   // 'and' or 'or'
        $post_types = get_post_types( $args, $output, $operator );
//      Unsetting unneeded post types
        unset( $post_types['attachment'] );
        unset( $post_types['page'] );

        return $post_types;
    }


    /**
     *
     * Main Functionality that post icon to Post Titles
     *
     */
    public function form( $title, $id = null ) {
        $pluginStatus = $this->get_pluginStatus();
        if ($pluginStatus == 'active') {
            $icon = $this->get_dashIcon();

            if (in_array(get_post_type($id), $this->get_selected_postTypes())) {
                switch ($this->get_positionIcon()) {
                    case 'positionIconRight':
                        return $title . '<span class="' . $icon . ' icon-plugin"></span>';
                        break;
                    case 'positionIconLeft':
                        return '<span class="' . $icon . ' icon-plugin"></span>' . $title;
                        break;
                    default:
                        return $title;
                }
            } else {
//            if not in selected Post Types return only Title
                return $title;
            }
        } else {
//            if plugin is disabled show only title
            return $title;
        }
    }
}

new postTitleIcon();