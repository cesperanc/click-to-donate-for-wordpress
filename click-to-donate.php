<?php
/*
Plugin Name: Click to donate
Description: This extension provides a system for managing image advertising campaigns based on clicks of visitors
Version: 0.18
Author: Cláudio Esperança, Diogo Serra
Author URI: http://dei.estg.ipleiria.pt/
*/

//namespace pt\ipleiria\estg\dei\pi\ClickToDonate;

if(!class_exists('ClickToDonate')):
    class ClickToDonate{
        // Constants
            /**
             * The query param to which this plugin will respond to 
             */
            const URL_QUERY_PARAM = 'donate-to';
            
            /**
             * The post type for the campaigns 
             */
            const POST_TYPE = 'ctd-campaign';
            
            /**
             * The database variable name to store the plugin database version
             */
            const DB_VERSION_FIELD_NAME = 'ClickToDonate_Database_version';
            
            // Table variables
            private static $tableClicks = 'clicks';
            private static $tableClicksID = 'ID';
            private static $tableClicksCampaignID = 'campaignID';
            private static $tableClicksBannerID = 'bannerID';
            private static $tableClicksUserID = 'userID';
            private static $tableClicksTimestamp = 'timestamp';
            
            private static $tableSponsoredCampaigns = 'sponsoredCampaign';
            private static $tableSponsoredCampaignsCampaignID = 'campaignID';
            private static $tableSponsoredCampaignsUserID = 'userID';

        // Methods
            /**
             * Class constructor 
             */
            public function __construct(){

            }
            
            /**
             * Load the plugin language pack, and register the post type for the campaigns
             */
            public function _init(){
                load_plugin_textdomain(__CLASS__, false, dirname(plugin_basename(__FILE__)).'/langs');
                
                register_post_type( self::POST_TYPE,
                    array(
                        'hierarchical' => false,
                        'labels' => array(
                            'name' => __('Campaigns', __CLASS__),
                            'singular_name' => __('Campaign', __CLASS__),
                            'add_new' => __('Add new', __CLASS__),
                            'add_new_item' => __('Add new campaign', __CLASS__),
                            'edit_item' => __('Edit campaign', __CLASS__),
                            'new_item' => __('New campaign', __CLASS__),
                            'view_item' => __('View campaign', __CLASS__),
                            'search_items' => __('Search campaigns', __CLASS__),
                            'not_found' => __('No campaign found', __CLASS__),
                            'not_found_in_trash' => __('No campaigns were found on the recycle bin', __CLASS__)
                        ),
                        'description' => __('Click to donate campaigns', __CLASS__),
                        'has_archive' => false,
                        'public' => true,
                        'publicly_queryable' => true,
                        'exclude_from_search' => true,
                        'show_ui' => true,
                        'show_in_menu' => true,
                        'show_in_nav_menus'=>true,
                        'supports'=>array('title', 'editor', 'thumbnail', 'revisions'),
                        'rewrite' => array(
                            'slug' => self::URL_QUERY_PARAM,
                            'with_front'=>'false'
                        ),
                        'query_var' => true,
                        'capability_type' => 'page',
                    )
                );
            }
            
            /**
             * Filter the posts content if they are campaigns, or count the visualizations
             * 
             * @param array $posts
             * @param WP_Query $query
             * @return array with the (possible) filtered posts 
             */
            function thePosts( $posts, $query ) {
                if ( empty( $posts ))
                    return $posts;
                
                foreach($posts as $index=>$post):
                    // If is a countable post type, is a single post and we are getting the post from the front office, verify and count the visit
                    if(get_post_type($post)==self::POST_TYPE && $query->is_single($post) && !is_admin()):
                        // @TODO Implement the code to verify if the banner can be shown and register the click. If the banner couldn't not be shown, replace the content by the error message
			// if not admin show			
			$views = get_post_custom_values(__CLASS__.'_views', $post->ID);
                        if(!empty($views) && isset($views[0])):
                            $views = $views[0];
                        endif;
                        update_post_meta($post->ID, __CLASS__.'_views', ((int)$views+1));
                        
                        $posts[$index]->post_content.="<hr/>Clique contabilizado";
                    endif;
                endforeach;
                
                return $posts;
            }
            
            /**
             * Register the scripts to be loaded on the backoffice, on our custom post type
             */
            public function adminEnqueueScripts() {
                if(is_admin() && ($current_screen = get_current_screen()) && $current_screen->post_type==self::POST_TYPE):
                    // Register the script
                    wp_enqueue_script(__CLASS__.'_admin', plugins_url('js/admin.js', __FILE__), array('jquery-ui-datepicker'), '1.0');

                    // Localize the script
                    wp_localize_script(__CLASS__.'_admin', 'ctdAdmin', array(
                        'closeText'=>__( 'Done', __CLASS__),
                        'currentText'=>__( 'Today', __CLASS__),
                        'dateFormat'=>__( 'mm/dd/yy', __CLASS__),
                        'dayNamesSunday'=>__( 'Sunday', __CLASS__),
                        'dayNamesMonday'=>__( 'Monday', __CLASS__),
                        'dayNamesTuesday'=>__( 'Tuesday', __CLASS__),
                        'dayNamesWednesday'=>__( 'Wednesday', __CLASS__),
                        'dayNamesThursday'=>__( 'Thursday', __CLASS__),
                        'dayNamesFriday'=>__( 'Friday', __CLASS__),
                        'dayNamesSaturday'=>__( 'Saturday', __CLASS__),
                        'dayNamesMinSu'=>__( 'Su', __CLASS__),
                        'dayNamesMinMo'=>__( 'Mo', __CLASS__),
                        'dayNamesMinTu'=>__( 'Tu', __CLASS__),
                        'dayNamesMinWe'=>__( 'We', __CLASS__),
                        'dayNamesMinTh'=>__( 'Th', __CLASS__),
                        'dayNamesMinFr'=>__( 'Fr', __CLASS__),
                        'dayNamesMinSa'=>__( 'Sa', __CLASS__),
                        'dayNamesShortSun'=>__( 'Sun', __CLASS__),
                        'dayNamesShortMon'=>__( 'Mon', __CLASS__),
                        'dayNamesShortTue'=>__( 'Tue', __CLASS__),
                        'dayNamesShortWed'=>__( 'Wed', __CLASS__),
                        'dayNamesShortThu'=>__( 'Thu', __CLASS__),
                        'dayNamesShortFri'=>__( 'Fri', __CLASS__),
                        'dayNamesShortSat'=>__( 'Sat', __CLASS__),
                        'monthNamesJanuary'=>__( 'January', __CLASS__),
                        'monthNamesFebruary'=>__( 'February', __CLASS__),
                        'monthNamesMarch'=>__( 'March', __CLASS__),
                        'monthNamesApril'=>__( 'April', __CLASS__),
                        'monthNamesMay'=>__( 'May', __CLASS__),
                        'monthNamesJune'=>__( 'June', __CLASS__),
                        'monthNamesJuly'=>__( 'July', __CLASS__),
                        'monthNamesAugust'=>__( 'August', __CLASS__),
                        'monthNamesSeptember'=>__( 'September', __CLASS__),
                        'monthNamesOctober'=>__( 'October', __CLASS__),
                        'monthNamesNovember'=>__( 'November', __CLASS__),
                        'monthNamesDecember'=>__( 'December', __CLASS__),
                        'monthNamesShortJan'=>__( 'Jan', __CLASS__),
                        'monthNamesShortFeb'=>__( 'Feb', __CLASS__),
                        'monthNamesShortMar'=>__( 'Mar', __CLASS__),
                        'monthNamesShortApr'=>__( 'Apr', __CLASS__),
                        'monthNamesShortMay'=>__( 'May', __CLASS__),
                        'monthNamesShortJun'=>__( 'Jun', __CLASS__),
                        'monthNamesShortJul'=>__( 'Jul', __CLASS__),
                        'monthNamesShortAug'=>__( 'Aug', __CLASS__),
                        'monthNamesShortSep'=>__( 'Sep', __CLASS__),
                        'monthNamesShortOct'=>__( 'Oct', __CLASS__),
                        'monthNamesShortNov'=>__( 'Nov', __CLASS__),
                        'monthNamesShortDec'=>__( 'Dec', __CLASS__),
                        'nextText'=>__( 'Next', __CLASS__),
                        'prevText'=>__( 'Prev', __CLASS__),
                        'weekHeader'=>__( 'Wk', __CLASS__)
                    ));
                endif;
            }
            
            /**
             * Register the styles to be loaded on the backoffice on our custom post type
             */
            public function adminPrintStyles() {
                if(is_admin() && ($current_screen = get_current_screen()) && $current_screen->post_type==self::POST_TYPE):
                    wp_enqueue_style('jQuery-ui', plugins_url('css/jquery-ui/smoothness/jquery.ui.all.css', __FILE__), array(), '1.8.20');
                endif;
            }
            
            /**
             * Add a metabox to the campaign post type
             */
            public function addMetaBox(){
                add_meta_box(__CLASS__.'-meta', __('Campaign configuration', __CLASS__), function(){
                    $postId = get_the_ID();

                    $views = get_post_custom_values(__CLASS__.'_views', $postId);
                    if(!empty($views) && isset($views[0])):
                        $views = $views[0];
                    endif;

                    $enableClicksLimit = get_post_custom_values(__CLASS__.'_enable_click_limits', $postId);
                    $maxClicks = get_post_custom_values(__CLASS__.'_maxClicks', $postId);

                    $enableStartDate = get_post_custom_values(__CLASS__.'_enable_startDate', $postId);
                    $startDate =  get_post_custom_values(__CLASS__.'_startDate', $postId);

                    $enableEndDate = get_post_custom_values(__CLASS__.'_enable_endDate', $postId);
                    $endDate =  get_post_custom_values(__CLASS__.'_endDate', $postId);
                    
                    // @TODO: add the time to the dates and validate the number format
                ?>
                    <fieldset id="ctd-enable-maxclicks-container" class="ctd-enable-container">
                        <legend><label class="selectit"><input id="ctd-enable-maxclicks" name="<?php echo(__CLASS__.'_enable_click_limits'); ?>" value="enable_click_limits"<?php checked('enable_click_limits', $enableClicksLimit[0]); ?> type="checkbox"/><?php _e('Limit the number of clicks', __CLASS__); ?></label></legend>
                        <div id="ctd-maxclicks-container" class="start-hidden"><label class="selectit"><?php _e('Clicks limit:', __CLASS__); ?> <input title="<?php esc_attr_e('Specify the number of clicks allowed before disabling the campaign', __CLASS__) ?>" id="ctd-maximum-clicks-limit" type="text" name="<?php echo(__CLASS__.'_maxClicks'); ?>" value="<?php echo($maxClicks[0]); ?>" /></label></div>
                    </fieldset>

                    <fieldset id="ctd-enable-startdate-container" class="ctd-enable-container">
                        <legend><label class="selectit"><input id="ctd-enable-startdate" name="<?php echo(__CLASS__.'_enable_startDate'); ?>" value="enable_startDate"<?php checked('enable_startDate', $enableStartDate[0]); ?> type="checkbox"/><?php _e('Set the campaign start date', __CLASS__); ?></label></legend>
                        <div id="ctd-startdate-container" class="start-hidden"><label class="selectit"><?php _e('Start date:', __CLASS__); ?> <input title="<?php esc_attr_e('Specify the start date when the campaign is supposed to start', __CLASS__) ?>" id="ctd-startdate" type="text" name="<?php echo(__CLASS__.'_startDate'); ?>" value="<?php echo($startDate[0]); ?>" /></label></div>
                    </fieldset>

                    <fieldset id="ctd-enable-enddate-container" class="ctd-enable-container">
                        <legend><label class="selectit"><input id="ctd-enable-enddate" name="<?php echo(__CLASS__.'_enable_endDate'); ?>" value="enable_endDate"<?php checked('enable_endDate', $enableEndDate[0]); ?> type="checkbox"/><?php _e('Set the campaign end date', __CLASS__); ?></label></legend>
                        <div id="ctd-enddate-container" class="start-hidden"><label class="selectit"><?php _e('End date:', __CLASS__); ?> <input title="<?php esc_attr_e('Specify the end date when the campaign is supposed to end', __CLASS__) ?>" id="ctd-enddate" type="text" name="<?php echo(__CLASS__.'_endDate'); ?>" value="<?php echo($endDate[0]); ?>" /></label></div>
                    </fieldset>

                    <div>
                        <?php printf(__( 'Views: %s', __CLASS__), $views); ?>
                    </div>
                <?php
                }, self::POST_TYPE, 'advanced', 'high');
            }
            
            /**
             * Save the custom data from the metaboxes with the custom post type
             * 
             * @param int $postId
             * @return int with the post id
             */
            public function savePost($postId){
                if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ):
                    return $postId;
                endif;
                switch(get_post_type($postId)):
                    case self::POST_TYPE:
                        // Get the posted data
                        if(isset($_POST[__CLASS__.'_enable_click_limits'])):
                            $enableClickLimits = $_POST[__CLASS__.'_enable_click_limits'];
                            $maxClicks = isset($_POST[__CLASS__.'_maxClicks'])?$_POST[__CLASS__.'_maxClicks']:-1;
                        else:
                            $enableClickLimits=false;
                            $maxClicks = -1;
                        endif;
                        
                        if(isset($_POST[__CLASS__.'_enable_startDate'])):
                            $enableStartDate = $_POST[__CLASS__.'_enable_startDate'];
                            $startDate = isset($_POST[__CLASS__.'_startDate'])?$_POST[__CLASS__.'_startDate']:'';
                        else:
                            $enableStartDate=false;
                            $startDate = '';
                        endif;
                        
                        if(isset($_POST[__CLASS__.'_enable_endDate'])):
                            $enableEndDate = $_POST[__CLASS__.'_enable_endDate'];
                            $endDate = isset($_POST[__CLASS__.'_endDate'])?$_POST[__CLASS__.'_endDate']:'';
                        else:
                            $enableEndDate=false;
                            $endDate = '';
                        endif;
                        
                        // Save the object in the database
                        update_post_meta($postId, __CLASS__.'_enable_click_limits', $enableClickLimits);
                        update_post_meta($postId, __CLASS__.'_maxClicks', $maxClicks);
                        update_post_meta($postId, __CLASS__.'_enable_startDate', $enableStartDate);
                        update_post_meta($postId, __CLASS__.'_startDate', $startDate);
                        update_post_meta($postId, __CLASS__.'_enable_endDate', $enableEndDate);
                        update_post_meta($postId, __CLASS__.'_endDate', $endDate);
                        
                        error_log($_POST[__CLASS__.'_enable_click_limits']);

                        break;
                endswitch;
                return $postId;
            }
            
            /**
             * Install the database tables
             */
            public static function install(){
                
                // Load the libraries
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                require_once(ABSPATH . 'wp-admin/includes/plugin.php');
                    
                // Load the plugin version
                $plugin = get_plugin_data(__FILE__);
                $version = $plugin['Version'];
                
                // Compare the plugin version with the local version, and update the database tables accordingly
                if(version_compare(get_option(self::DB_VERSION_FIELD_NAME), $version, '<')):
                    
                    // cache the errors
                    ob_start();
                
                    // Remove the previous version of the database (fine by now, but should be reconsidered in future versions)
                    //call_user_func(array(__CLASS__, 'uninstall'));
                    
                    // Get the WordPress database abstration layer instance
                    $wpdb = self::getWpDB();
                    
                    // Set the charset collate
                    $charset_collate = '';
                    if (!empty($wpdb->charset)):
                        $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
                    endif;
                    if (!empty($wpdb->collate)):
                        $charset_collate .= " COLLATE {$wpdb->collate}";
                    endif;
                    
                    $self = new self();
                    $prefix = self::getWpDB()->prefix;
                    
                    // Prepare the SQL queries for sponsored campaigns
                    $queries = array();
                    $queries[] = "
                        CREATE TABLE IF NOT EXISTS `{$self::$tableSponsoredCampaigns}` (
                            `{$self::$tableSponsoredCampaignsCampaignID}` bigint(20) unsigned NOT NULL COMMENT 'Foreign key for the campaign',
                            `{$self::$tableSponsoredCampaignsUserID}` bigint(20) unsigned NOT NULL COMMENT 'Foreign key for the user',
                            KEY `{$self::$tableSponsoredCampaignsUserID}` (`{$self::$tableSponsoredCampaignsUserID}`),
                            KEY `{$self::$tableSponsoredCampaignsCampaignID}` (`{$self::$tableSponsoredCampaignsCampaignID}`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Implementation of the campaigns-sponsors relationship';
                    ";
                    /*// Wordpress doesn't enforce the InnoDB MySQL engine to use on their tables, so we cannot enable the foreign key constrainsts until the Wordpress requires the MySQL 5.5 as the minimum requirement
                    $queries[] = "
                        ALTER TABLE `{$self::$tableSponsoredCampaigns}`
                            ADD CONSTRAINT `sponsoredCampaign_ibfk_1` FOREIGN KEY (`{$self::$tableSponsoredCampaignsCampaignID}`) REFERENCES `{$prefix}posts` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
                            ADD CONSTRAINT `sponsoredCampaign_ibfk_2` FOREIGN KEY (`{$self::$tableSponsoredCampaignsUserID}`) REFERENCES `{$prefix}users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
                        ;
                    ";
                     */
                    dbDelta($queries);
                    
                    // Prepare the SQL queries for clicks
                    $queries = array();
                    $queries[] = "
                        CREATE TABLE IF NOT EXISTS `{$self::$tableClicks}` (
                            `{$self::$tableClicksID}` bigint(20) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Key for the click',
                            `{$self::$tableClicksCampaignID}` bigint(20) unsigned DEFAULT NULL COMMENT 'Foreign key of the campaign',
                            `{$self::$tableClicksBannerID}` bigint(20) unsigned DEFAULT NULL COMMENT 'Foreign key of the banner',
                            `{$self::$tableClicksUserID}` bigint(20) unsigned DEFAULT NULL COMMENT 'Foreign key of the user',
                            `{$self::$tableClicksTimestamp}` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Time stamp of the click',
                            PRIMARY KEY (`{$self::$tableClicksID}`),
                            KEY `{$self::$tableClicksBannerID}` (`{$self::$tableClicksBannerID}`),
                            KEY `{$self::$tableClicksUserID}` (`{$self::$tableClicksUserID}`),
                            KEY `{$self::$tableClicksCampaignID}` (`{$self::$tableClicksCampaignID}`)
                        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Table to store the site clicks on campaigns and/or banners' AUTO_INCREMENT=1 ;
                    ";
                    /*// Wordpress doesn't enforce the InnoDB MySQL engine to use on their tables, so we cannot enable the foreign key constrainsts until the Wordpress requires the MySQL 5.5 as the minimum requirement
                    $queries[] = "
                        ALTER TABLE `{$self::$tableClicks}`
                            ADD CONSTRAINT `clicks_ibfk_3` FOREIGN KEY (`{$self::$tableClicksUserID}`) REFERENCES `{$prefix}users` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
                            ADD CONSTRAINT `clicks_ibfk_1` FOREIGN KEY (`{$self::$tableClicksCampaignID}`) REFERENCES `{$prefix}posts` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
                            ADD CONSTRAINT `clicks_ibfk_2` FOREIGN KEY (`{$self::$tableClicksBannerID}`) REFERENCES `{$prefix}posts` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE
                        ;
                    ";
                     */
                    dbDelta($queries);
                    
                    // If errors were triggered, output them
                    $contents = ob_get_contents();
                    if(!empty($contents)):
                        trigger_error($contents,E_USER_ERROR);
                    endif;
                    
                    // Update the plugin DB version
                    update_option(self::DB_VERSION_FIELD_NAME, $version);
                endif;
            }
            
            /**
             * Uninstall the plugin data
             */
            public static function uninstall(){
                // Get the WordPress database abstration layer instance
                $wpdb = self::getWpDB();
                $self = new self();
                
                $wpdb->query("DROP TABLE IF EXISTS `{$self::$tableClicks}`;");
                $wpdb->query("DROP TABLE IF EXISTS `{$self::$tableSponsoredCampaigns}`;");
                
                // Remove the plugin version information
                delete_option(self::DB_VERSION_FIELD_NAME);
                
                // Remove all the campaigns
                self::removePostType();
                
                // @TODO: remove all the metadata from the wordpress tables e.g., user table
                
            }
            
            
        // Static methods
            /**
             * Remove the custom post type for this plugin
             * 
             * @global array $wp_post_types with all the custom post types
             * @return boolean true on success, false otherwise
             */
            private static function removePostType() {
                global $wp_post_types;
                
                $posts = get_posts( array(
                    'post_type' => self::POST_TYPE,
                    'posts_per_page' => -1,
                    'nopaging' => true
                ) );
                
                foreach ($posts as $post):
                    wp_delete_post($post->ID, true);
                endforeach;
                
                
                if ( isset( $wp_post_types[ self::POST_TYPE ] ) ):
                    unset( $wp_post_types[ self::POST_TYPE ] );
                    return true;
                endif;
                return false;
            }

            /**
             * Return the WordPress Database Access Abstraction Object 
             * 
             * @global wpdb $wpdb
             * @return wpdb 
             */
            public static function getWpDB(){
                global $wpdb;
                
                return $wpdb;
            }
        
            /**
             * Register the plugin functions with the Wordpress hooks
             */
            public static function init(){
                $prefix = self::getWpDB()->prefix;
                // Append the Wordpress table prefix to the table names (if the prefix isn't already added)
                self::$tableClicks = (stripos(self::$tableClicks, $prefix)===0?'':$prefix).self::$tableClicks;
                self::$tableSponsoredCampaigns = (stripos(self::$tableSponsoredCampaigns, $prefix)===0?'':$prefix).self::$tableSponsoredCampaigns;
                
                // Register the install database method to be executed when the plugin is activated
                register_activation_hook(__FILE__,array(__CLASS__, 'install'));
                
                // Register the install database method to be executed when the plugin is updated
                add_action('plugins_loaded', array(__CLASS__, 'install'));

                // Register the remove database method when the plugin is removed
                register_uninstall_hook(__FILE__,array(__CLASS__, 'uninstall'));

                // Register the _init method to the Wordpress initialization action hook
                add_action('init', array(__CLASS__, '_init'));
                
                // Register the addMetaBox method to the Wordpress backoffice administration initialization action hook
                add_action('admin_init', array(__CLASS__, 'addMetaBox'));
                
                // Register the savePost method to the Wordpress save_post action hook
                add_action('save_post', array(__CLASS__, 'savePost'));
                
                // Add thePosts method to filter the_posts
                add_filter('the_posts', array(__CLASS__, 'thePosts'), 10, 2);
                
                
                
                // Register the adminEnqueueScripts method to the Wordpress admin_enqueue_scripts action hook
                add_action('admin_enqueue_scripts', array(__CLASS__, 'adminEnqueueScripts'));
                
                // Register the adminPrintStyles method to the Wordpress admin_print_styles action hook
                add_action('admin_print_styles', array(__CLASS__, 'adminPrintStyles'));
                
            }
        }
endif;

ClickToDonate::init();