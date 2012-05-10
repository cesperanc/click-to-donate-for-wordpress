<?php
/*
  Plugin Name: Click to donate
  Description: This extension provides a system for managing image advertising campaigns based on clicks of visitors
  Version: 0.18
  Author: Cláudio Esperança, Diogo Serra
  Author URI: http://dei.estg.ipleiria.pt/
 */

//namespace pt\ipleiria\estg\dei\pi\ClickToDonate;

if (!class_exists('ClickToDonate')):

    class ClickToDonate {
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
        const STATUS_online = 'ctd-online';
        const STATUS_finished = 'ctd-finished';
        const STATUS_scheduled = 'ctd-scheduled';
        const STATUS_unavailable = 'ctd-unavailable';
        
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
        
        private static $enableCoolOff = '_enable_cool_off';
        private static $coolOff = '_cool_off_time';
        private static $restrictByCookie = '_restrict_by_cookie';
        private static $restrictByLogin = '_restrict_by_login';
        private static $enableClickLimits = '_enable_click_limits';
        private static $maxClicks = '_maxClicks';
        private static $enableStartDate = '_enable_startDate';
        private static $startDate = '_startDate';
        private static $enableEndDate = '_enable_endDate';
        private static $endDate = '_endDate';

        // Methods
        /**
         * Class constructor 
         */
        public function __construct() {
            
        }
        
        /**
         * Load the plugin language pack, and register the post type for the campaigns
         */
        public function _init() {
            load_plugin_textdomain(__CLASS__, false, dirname(plugin_basename(__FILE__)) . '/langs');

            register_post_type(self::POST_TYPE, array(
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
                'show_in_nav_menus' => true,
                'supports' => array('title', 'editor', 'thumbnail', 'revisions'),
                'rewrite' => array(
                    'slug' => self::URL_QUERY_PARAM,
                    'with_front' => 'false'
                ),
                'query_var' => true,
                /* // Maybe we can use this in the module 2
                'capability_type' => self::POST_TYPE,
                'capabilities' => array(
                    //'publish_posts' => 'publish_' . self::POST_TYPE,
                    'edit_posts' => 'edit_pages',
                    'edit_others_posts' => 'edit_others_pages',
                    'delete_posts' => 'delete_pages',
                    'delete_others_posts' => 'delete_others_pages',
                    'read_private_posts' => 'read_private_pages',
                    'edit_post' => 'edit_pages',
                    'delete_post' => 'delete_pages',
                    'read_post' => 'read_pages'
                )
                */
            ));
            
            if(!post_type_exists(self::STATUS_online)):
                register_post_status( self::STATUS_online, array(
                    'label' => __( 'Online', __CLASS__ ),
                    'public' => true,
                    'internal'=>false,
                    'private'=>false,
                    'exclude_from_search' => true,
                    'show_in_admin_all_list' => true,
                    'show_in_admin_status_list' => true,
                    'label_count' => _n_noop( 'Online <span class="count">(%s)</span>', 'Online <span class="count">(%s)</span>' ),
                ) );
            endif;
            
            if(!post_type_exists(self::STATUS_scheduled)):
                register_post_status( self::STATUS_scheduled, array(
                    'label' => __( 'Scheduled', __CLASS__ ),
                    'public' => false,
                    'internal'=>false,
                    'private'=>true,
                    'exclude_from_search' => true,
                    'show_in_admin_all_list' => true,
                    'show_in_admin_status_list' => true,
                    'label_count' => _n_noop( 'Scheduled <span class="count">(%s)</span>', 'Scheduled <span class="count">(%s)</span>' ),
                ) );
            endif;
            
            if(!post_type_exists(self::STATUS_finished)):
                register_post_status( self::STATUS_finished, array(
                    'label' => __( 'Finished', __CLASS__ ),
                    'public' => false,
                    'internal'=>false,
                    'private'=>true,
                    'exclude_from_search' => true,
                    'show_in_admin_all_list' => true,
                    'show_in_admin_status_list' => true,
                    'label_count' => _n_noop( 'Finished <span class="count">(%s)</span>', 'Finished <span class="count">(%s)</span>' ),
                ) );
            endif;
            
            if(!post_type_exists(self::STATUS_unavailable)):
                register_post_status( self::STATUS_unavailable, array(
                    'label' => __( 'Unavailable', __CLASS__ ),
                    'public' => false,
                    'internal'=>false,
                    'private'=>true,
                    'exclude_from_search' => true,
                    'show_in_admin_all_list' => true,
                    'show_in_admin_status_list' => true,
                    'label_count' => _n_noop( 'Unavailable <span class="count">(%s)</span>', 'Unavailable <span class="count">(%s)</span>' ),
                ) );
            endif;
        }
// Maybe we can use this in the module 2
//        public function mapMetaCapabilities($caps, $cap, $userId, $args) {
//            // If we are checking for the publish capability on our post type, return empty capabities to block the content publication (we will be using our own mechanism)
//            if ('publish_' . self::POST_TYPE == $cap):
//                return array('do_not_allow');
//            endif;
//
//            return $caps;
//        }

        /**
         * Filter the posts content if they are campaigns, or count the visualizations
         * 
         * @param array $posts
         * @param WP_Query $query
         * @return array with the (possible) filtered posts 
         */
        public function thePosts($posts, $query) {
            if (empty($posts))
                return $posts;

            foreach ($posts as $index => $post):
                // If is a countable post type, is a single post and we are getting the post from the front office, verify and count the visit
                if (get_post_type($post) == self::POST_TYPE && $query->is_single($post) && !is_admin()):
                    // @TODO Implement the code to verify if the banner can be shown and register the click. If the banner couldn't not be shown, replace the content by the error message
                    // if not admin show			
                    $views = get_post_custom_values(__CLASS__ . '_views', $post->ID);
                    if (!empty($views) && isset($views[0])):
                        $views = $views[0];
                    endif;
                    update_post_meta($post->ID, __CLASS__ . '_views', ((int) $views + 1));

                    $posts[$index]->post_content.="<hr/>Clique contabilizado";
                endif;
            endforeach;

            return $posts;
        }

        /**
         * Register the scripts to be loaded on the backoffice, on our custom post type
         */
        public function adminEnqueueScripts() {
            if (is_admin() && ($current_screen = get_current_screen()) && $current_screen->post_type == self::POST_TYPE /*&& $current_screen->base=='post'*/):
                // Register the scripts
                wp_enqueue_script('ui-spinner', plugins_url('js/jquery-ui/ui.spinner.min.js', __FILE__), array('jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse'), '1.20');
                wp_enqueue_script(__CLASS__ . '_admin', plugins_url('js/admin.js', __FILE__), array('jquery-ui-datepicker', 'ui-spinner'), '1.0');

                // Localize the script
                wp_localize_script(__CLASS__ . '_admin', 'ctdAdmin', array(
                    'closeText' => __('Done', __CLASS__),
                    'currentText' => __('Today', __CLASS__),
                    'dateFormat' => __('mm/dd/yy', __CLASS__),
                    'dayNamesSunday' => __('Sunday', __CLASS__),
                    'dayNamesMonday' => __('Monday', __CLASS__),
                    'dayNamesTuesday' => __('Tuesday', __CLASS__),
                    'dayNamesWednesday' => __('Wednesday', __CLASS__),
                    'dayNamesThursday' => __('Thursday', __CLASS__),
                    'dayNamesFriday' => __('Friday', __CLASS__),
                    'dayNamesSaturday' => __('Saturday', __CLASS__),
                    'dayNamesMinSu' => __('Su', __CLASS__),
                    'dayNamesMinMo' => __('Mo', __CLASS__),
                    'dayNamesMinTu' => __('Tu', __CLASS__),
                    'dayNamesMinWe' => __('We', __CLASS__),
                    'dayNamesMinTh' => __('Th', __CLASS__),
                    'dayNamesMinFr' => __('Fr', __CLASS__),
                    'dayNamesMinSa' => __('Sa', __CLASS__),
                    'dayNamesShortSun' => __('Sun', __CLASS__),
                    'dayNamesShortMon' => __('Mon', __CLASS__),
                    'dayNamesShortTue' => __('Tue', __CLASS__),
                    'dayNamesShortWed' => __('Wed', __CLASS__),
                    'dayNamesShortThu' => __('Thu', __CLASS__),
                    'dayNamesShortFri' => __('Fri', __CLASS__),
                    'dayNamesShortSat' => __('Sat', __CLASS__),
                    'monthNamesJanuary' => __('January', __CLASS__),
                    'monthNamesFebruary' => __('February', __CLASS__),
                    'monthNamesMarch' => __('March', __CLASS__),
                    'monthNamesApril' => __('April', __CLASS__),
                    'monthNamesMay' => __('May', __CLASS__),
                    'monthNamesJune' => __('June', __CLASS__),
                    'monthNamesJuly' => __('July', __CLASS__),
                    'monthNamesAugust' => __('August', __CLASS__),
                    'monthNamesSeptember' => __('September', __CLASS__),
                    'monthNamesOctober' => __('October', __CLASS__),
                    'monthNamesNovember' => __('November', __CLASS__),
                    'monthNamesDecember' => __('December', __CLASS__),
                    'monthNamesShortJan' => __('Jan', __CLASS__),
                    'monthNamesShortFeb' => __('Feb', __CLASS__),
                    'monthNamesShortMar' => __('Mar', __CLASS__),
                    'monthNamesShortApr' => __('Apr', __CLASS__),
                    'monthNamesShortMay' => __('May', __CLASS__),
                    'monthNamesShortJun' => __('Jun', __CLASS__),
                    'monthNamesShortJul' => __('Jul', __CLASS__),
                    'monthNamesShortAug' => __('Aug', __CLASS__),
                    'monthNamesShortSep' => __('Sep', __CLASS__),
                    'monthNamesShortOct' => __('Oct', __CLASS__),
                    'monthNamesShortNov' => __('Nov', __CLASS__),
                    'monthNamesShortDec' => __('Dec', __CLASS__),
                    'nextText' => __('Next', __CLASS__),
                    'prevText' => __('Prev', __CLASS__),
                    'weekHeader' => __('Wk', __CLASS__)
                ));
            endif;
        }

        /**
         * Register the styles to be loaded on the backoffice on our custom post type
         */
        public function adminPrintStyles() {
            if (is_admin() && ($current_screen = get_current_screen()) && $current_screen->post_type == self::POST_TYPE):
                wp_enqueue_style('jquery-ui-core', plugins_url('css/jquery-ui/smoothness/jquery.ui.core.css', __FILE__), array(), '1.8.20');
                wp_enqueue_style('jquery-ui-datepicker', plugins_url('css/jquery-ui/smoothness/jquery.ui.datepicker.css', __FILE__), array('jquery-ui-core'), '1.8.20');
                wp_enqueue_style('jquery-ui-theme', plugins_url('css/jquery-ui/smoothness/jquery.ui.theme.css', __FILE__), array('jquery-ui-core'), '1.8.20');
                wp_enqueue_style('ui-spinner', plugins_url('css/jquery-ui/ui.spinner.css', __FILE__), array(), '1.20');
            endif;
        }

        /**
         * Add a metabox to the campaign post type
         */
        public function addMetaBox() {
            
            // Replace the submit core metabox by ours
            add_meta_box('submitdiv', __('Campaign configuration'), function($post) {
                $post_type = $post->post_type;
                $post_type_object = get_post_type_object($post_type);
                $can_publish = current_user_can($post_type_object->cap->publish_posts);
                ?>
                <div class="submitbox" id="submitpost">

                    <div id="minor-publishing">

                        <?php // Hidden submit button early on so that the browser chooses the right button when form is submitted with Return key  ?>
                        <div style="display:none;">
                            <?php submit_button(__('Save', __CLASS__), 'button', 'save'); ?>
                        </div>

                        <div id="minor-publishing-actions">
                            <div id="save-action">
                                <?php if (in_array($post->post_status, array(self::STATUS_unavailable, 'auto-draft')) || 0 == $post->ID): ?>
                                    <input type="submit" name="save" id="save-post" value="<?php esc_attr_e('Save', __CLASS__); ?>" tabindex="4" class="button button-highlighted" />
                                <?php endif; ?>
                                <img src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" class="ajax-loading" id="draft-ajax-loading" alt="" />
                            </div>
                            <div id="preview-action">
                                <?php
                                    if (in_array($post->post_status, array(self::STATUS_online, self::STATUS_finished, self::STATUS_scheduled))):
                                        $preview_link = esc_url(get_permalink($post->ID));
                                        $preview_button = __('Preview Changes', __CLASS__);
                                    else:
                                        $preview_link = get_permalink($post->ID);
                                        if (is_ssl())
                                            $preview_link = str_replace('http://', 'https://', $preview_link);
                                        $preview_link = esc_url(apply_filters('preview_post_link', add_query_arg('preview', 'true', $preview_link)));
                                        $preview_button = __('Preview', __CLASS__);
                                    endif;
                                ?>
                                <a class="preview button" href="<?php echo($preview_link); ?>" target="wp-preview" id="post-preview" tabindex="4"><?php echo($preview_button); ?></a>
                                <input type="hidden" name="wp-preview" id="wp-preview" value="" />
                            </div>

                            <div class="clear"></div>
                        </div><?php // /minor-publishing-actions  ?>

                        <?php 
                            // retrieve the campaign data
                            $startDate = self::getStartDate($post->ID);

                            $endDate = self::getEndDate($post->ID);
                            
                            // Extract the hours from the timestamp
                            if(!self::hasStartDate($post->ID)):
                                $startHours = array('0');
                                $startMinutes = array('00');
                            else:
                                $startHours = array(date('G', $startDate));
                                $startMinutes = array(date('i', $startDate));
                            endif;
                            
                            // Extract the minutes from the timestamp
                            if(!self::hasEndDate($post->ID)):
                                $endHours = array('0');
                                $endMinutes = array('00');
                            else:
                                $endHours = array(date('G', $endDate));
                                $endMinutes = array(date('i', $endDate));
                            endif;
                        ?>
                        <div id="ctd-campaign-admin" class="hide-if-no-js misc-pub-section">
                            <fieldset id="ctd-enable-cool-off-container" class="ctd-enable-container">
                                <legend><input id="ctd-enable-cool-off" name="<?php echo(__CLASS__.self::$enableCoolOff); ?>" value="enable_cool_off"<?php checked(self::hasCoolOffLimit($post->ID)); ?> type="checkbox"/><label class="selectit" for="ctd-enable-cool-off"><?php _e('Cooling-off period', __CLASS__); ?></label></legend>
                                <div id="ctd-cool-off-container" class="start-hidden">
                                    <div><label class="selectit"><?php _e('Cooling-off period:', __CLASS__); ?> <input title="<?php esc_attr_e('Specify the number of seconds between visits on the same campaign', __CLASS__) ?>" id="ctd-cool-off-period" type="text" name="<?php echo(__CLASS__.self::$coolOff); ?>" value="<?php echo(self::getCoolOffLimit($post->ID)); ?>" /></label><span id="ctd-readable-cool-off-period"></span></div>
                                    <div><input id="ctd-restrict-by-cookie" name="<?php echo(__CLASS__.self::$restrictByCookie); ?>" value="restrict_by_cookie"<?php checked(self::isToRestrictByCookie($post->ID)); ?> type="checkbox"/><label class="selectit" for="ctd-restrict-by-cookie"><?php _e('Restrict by cookie', __CLASS__); ?></label></div>
                                    <div><input id="ctd-restrict-by-login" name="<?php echo(__CLASS__.self::$restrictByLogin); ?>" value="restrict_by_login"<?php checked(self::isToRestrictByLogin($post->ID)); ?> type="checkbox"/><label class="selectit" for="ctd-restrict-by-login"><?php _e('Restrict by login', __CLASS__); ?></label></div>
                                </div>

                            </fieldset>
                            
                            <fieldset id="ctd-enable-maxclicks-container" class="ctd-enable-container">
                                <legend><input id="ctd-enable-maxclicks" name="<?php echo(__CLASS__.self::$enableClickLimits); ?>" value="enable_click_limits"<?php checked(self::hasClicksLimit($post->ID)); ?> type="checkbox"/><label class="selectit" for="ctd-enable-maxclicks"><?php _e('Limit the number of clicks', __CLASS__); ?></label></legend>
                                <div id="ctd-maxclicks-container" class="start-hidden">
                                    <label class="selectit"><?php _e('Clicks limit:', __CLASS__); ?> <input title="<?php esc_attr_e('Specify the number of clicks allowed before disabling the campaign', __CLASS__) ?>" id="ctd-maximum-clicks-limit" type="text" name="<?php echo(__CLASS__.self::$maxClicks); ?>" value="<?php echo(self::getClicksLimit($post->ID)); ?>" /></label>
                                </div>

                            </fieldset>

                            <fieldset id="ctd-enable-startdate-container" class="ctd-enable-container">
                                <legend>
                                    <input id="ctd-enable-startdate" name="<?php echo(__CLASS__ . self::$enableStartDate); ?>" value="enable_startDate"<?php checked(self::hasStartDate($post->ID)); ?> type="checkbox"/><label class="selectit" for="ctd-enable-startdate"><?php _e('Set the campaign start date', __CLASS__); ?></label>
                                </legend>
                                <div id="ctd-startdate-container" class="start-hidden">
                                    <label class="selectit"><?php _e('Start date:', __CLASS__); ?> <input style="width: 6em;" size="8" maxlength="10" title="<?php esc_attr_e('Specify the start date when the campaign is supposed to start', __CLASS__) ?>" id="ctd-startdate" type="text" /></label>
                                    <input id="ctd-hidden-startdate" type="hidden" name="<?php echo(__CLASS__ . self::$startDate); ?>" value="<?php echo(gmdate('Y-n-j', $startDate)); ?>" />
                                    @<input title="<?php esc_attr_e('Specify the campaign starting hours', __CLASS__) ?>" style="width: 2em;" size="2" maxlength="2" id="ctd-starthours" name="<?php echo(__CLASS__ . '_startHours'); ?>" type="text" value="<?php echo($startHours[0]); ?>" />:<input title="<?php esc_attr_e('Specify the campaign starting minutes', __CLASS__) ?>" style="width: 2em;" size="2" maxlength="2" id="ctd-startminutes" name="<?php echo(__CLASS__ . '_startMinutes'); ?>" type="text" value="<?php echo($startMinutes[0]); ?>" />
                                </div>
                            </fieldset>

                            <fieldset id="ctd-enable-enddate-container" class="ctd-enable-container">
                                <legend>
                                    <input id="ctd-enable-enddate" name="<?php echo(__CLASS__ . self::$enableEndDate); ?>" value="enable_endDate"<?php checked(self::hasEndDate($post->ID)); ?> type="checkbox"/><label class="selectit" for="ctd-enable-enddate"><?php _e('Set the campaign end date', __CLASS__); ?></label>
                                </legend>
                                <div id="ctd-enddate-container" class="start-hidden">
                                    <label class="selectit"><?php _e('End date:', __CLASS__); ?> <input style="width: 6em;" size="8" maxlength="10" title="<?php esc_attr_e('Specify the end date when the campaign is supposed to end', __CLASS__) ?>" id="ctd-enddate" type="text" name="<?php echo(__CLASS__ . self::$endDate); ?>" /></label>
                                    <input id="ctd-hidden-enddate" type="hidden" name="<?php echo(__CLASS__ . self::$endDate); ?>" value="<?php echo(gmdate('Y-n-j', $endDate)); ?>" />
                                    @<input title="<?php esc_attr_e('Specify the campaign ending hours', __CLASS__) ?>" style="width: 2em;" size="2" maxlength="2" id="ctd-endhours" name="<?php echo(__CLASS__ . '_endHours'); ?>" type="text" value="<?php echo($endHours[0]); ?>" />:<input title="<?php esc_attr_e('Specify the campaign ending minutes', __CLASS__) ?>" style="width: 2em;" size="2" maxlength="2" id="ctd-endminutes" name="<?php echo(__CLASS__ . '_endMinutes'); ?>" type="text" value="<?php echo($endMinutes[0]); ?>" />
                                </div>
                            </fieldset>
                        </div>
                        
                        <div id="misc-publishing-actions">

                            <div class="misc-pub-section<?php if (!$can_publish) { echo ' misc-pub-section-last'; } ?>">
                                <label for="post_status"><?php _e('Status:', __CLASS__) ?></label>
                                <input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo(esc_attr(('auto-draft' == $post->post_status ) ? self::STATUS_unavailable : $post->post_status)); ?>" />
                                <select name='post_status' id='post_status' tabindex='4'>
                                    <option<?php selected($post->post_status, self::STATUS_online); ?> value='<?php echo(self::STATUS_online); ?>'><?php _e('Online', __CLASS__) ?></option>
                                    <option<?php selected($post->post_status, self::STATUS_finished); ?> value='<?php echo(self::STATUS_finished); ?>'><?php _e('Finished', __CLASS__) ?></option>
                                    <option<?php selected($post->post_status, self::STATUS_scheduled); ?> value='<?php echo(self::STATUS_scheduled); ?>'><?php _e('Scheduled', __CLASS__) ?></option>
                                    <?php if ('auto-draft' == $post->post_status) : ?>
                                        <option<?php selected($post->post_status, 'auto-draft'); ?> value='<?php echo(self::STATUS_unavailable); ?>'><?php _e('Unavailable', __CLASS__) ?></option>
                                    <?php else : ?>
                                        <option<?php selected($post->post_status, self::STATUS_unavailable); ?> value='<?php echo(self::STATUS_unavailable); ?>'><?php _e('Unavailable', __CLASS__) ?></option>
                                    <?php endif; ?>
                                </select>
                            </div><?php // /misc-pub-section  ?>
                            <?php do_action('post_submitbox_misc_actions'); ?>
                        </div>
                        <div class="clear"></div>
                    </div>
                    
                    

                    <div id="major-publishing-actions">
                            <?php do_action('post_submitbox_start'); ?>
                        <div id="delete-action">
                            <?php
                            if (current_user_can("delete_post", $post->ID)) {
                                if (!EMPTY_TRASH_DAYS)
                                    $delete_text = __('Delete Permanently', __CLASS__);
                                else
                                    $delete_text = __('Move to Trash', __CLASS__);
                                ?>
                                <a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo($delete_text); ?></a><?php }
                ?>
                        </div>

                        <div id="publishing-action">
                            <img src="<?php echo esc_url(admin_url('images/wpspin_light.gif')); ?>" class="ajax-loading" id="ajax-loading" alt="" />
                            <?php
                                if (in_array($post->post_status, array(self::STATUS_unavailable, 'auto-draft')) || 0 == $post->ID):
                                    if ($can_publish) :
                                        if (!empty($startDate) && time() < $startDate) :
                                            ?>
                                            <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Schedule', __CLASS__) ?>" />
                                            <?php submit_button(__('Schedule', __CLASS__), 'primary', 'publish', false, array('tabindex' => '5', 'accesskey' => 'p')); ?>
                                        <?php else : ?>
                                            <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish', __CLASS__) ?>" />
                                            <?php submit_button(__('Publish', __CLASS__), 'primary', 'publish', false, array('tabindex' => '5', 'accesskey' => 'p')); ?>
                                        <?php endif;
                                    endif;
                                else:
                                    ?>
                                    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update', __CLASS__) ?>" />
                                    <input name="save" type="submit" class="button-primary" id="publish" tabindex="5" accesskey="p" value="<?php esc_attr_e('Update', __CLASS__) ?>" />
                                    <?php 
                                endif;
                            ?>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>

                <?php
            }, self::POST_TYPE, 'side', 'core');
        }

        /**
         * Save the custom data from the metaboxes with the custom post type
         * 
         * @param int $postId
         * @return int with the post id
         */
        public function savePost($postId) {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE):
                return $postId;
            endif;
            switch (get_post_type($postId)):
                case self::POST_TYPE:
                    // Get the submited data
                    if (isset($_POST[__CLASS__ . self::$enableCoolOff])):
                        $enableCoolOff = $_POST[__CLASS__.self::$enableCoolOff];
                        $coolOff = isset($_POST[__CLASS__.self::$coolOff]) ? $_POST[__CLASS__.self::$coolOff] : -1;
                        $restrictByCookie = isset($_POST[__CLASS__.self::$restrictByCookie]) ? $_POST[__CLASS__.self::$restrictByCookie] : false;
                        $restrictByLogin = isset($_POST[__CLASS__.self::$restrictByLogin]) ? $_POST[__CLASS__.self::$restrictByLogin] : false;
                    else:
                        $enableCoolOff = false;
                        $coolOff = -1;
                        $restrictByCookie = false;
                        $restrictByLogin = false;
                    endif;
                    
                    if (isset($_POST[__CLASS__ . self::$enableClickLimits])):
                        $enableClickLimits = $_POST[__CLASS__.self::$enableClickLimits];
                        $maxClicks = isset($_POST[__CLASS__.self::$maxClicks]) ? $_POST[__CLASS__.self::$maxClicks] : -1;
                    else:
                        $enableClickLimits = false;
                        $maxClicks = -1;
                    endif;

                    if (isset($_POST[__CLASS__ . self::$enableStartDate])):
                        $enableStartDate = $_POST[__CLASS__ . self::$enableStartDate];
                    
                        $startDate = isset($_POST[__CLASS__ . self::$startDate]) ? ($_POST[__CLASS__ . self::$startDate]) : '';
                        list($year, $month, $day) = explode('-', $startDate);
                        $hours = isset($_POST[__CLASS__ . '_startHours']) ? (int)$_POST[__CLASS__ . '_startHours'] : 0;
                        $minutes = isset($_POST[__CLASS__ . '_startMinutes']) ? (int)$_POST[__CLASS__ . '_startMinutes'] : 0;
                        $startDate = gmmktime($hours, $minutes, 0, $month, $day, $year);
                    else:
                        $enableStartDate = false;
                        $startDate = '';
                    endif;

                    if (isset($_POST[__CLASS__ . self::$enableEndDate])):
                        $enableEndDate = $_POST[__CLASS__ . self::$enableEndDate];
                        $endDate = isset($_POST[__CLASS__ . self::$endDate]) ? ($_POST[__CLASS__ . self::$endDate]) : '';
                        list($year, $month, $day) = explode('-', $endDate);
                        $hours = isset($_POST[__CLASS__ . '_endHours']) ? (int)$_POST[__CLASS__ . '_endHours'] : 0;
                        $minutes = isset($_POST[__CLASS__ . '_endMinutes']) ? (int)$_POST[__CLASS__ . '_endMinutes'] : 0;
                        $endDate = gmmktime($hours, $minutes, 0, $month, $day, $year);
                    else:
                        $enableEndDate = false;
                        $endDate = '';
                    endif;
                    
                    // The start date cannot be greater than end date
                    if(is_numeric($startDate) && is_numeric($endDate) && $startDate>$endDate):
                        $t = $startDate;
                        $startDate = $endDate;
                        $endDate = $t;
                    endif;

                    // Save the metadata to the database
                    self::setPostCustomValues(self::$enableCoolOff, $enableCoolOff);
                    self::setPostCustomValues(self::$coolOff, $coolOff);
                    self::setPostCustomValues(self::$restrictByCookie, $restrictByCookie);
                    self::setPostCustomValues(self::$restrictByLogin, $restrictByLogin);
                    self::setPostCustomValues(self::$enableClickLimits, $enableClickLimits);
                    self::setPostCustomValues(self::$maxClicks, $maxClicks);
                    self::setPostCustomValues(self::$enableStartDate, $enableStartDate);
                    self::setPostCustomValues(self::$startDate, $startDate);
                    self::setPostCustomValues(self::$enableEndDate, $enableEndDate);
                    self::setPostCustomValues(self::$endDate, $endDate);
                    
                    // Change the post status based on the metadata
                    self::updatePostStatus($postId);

                    break;
            endswitch;
            return $postId;
        }
        
        /**
         * If the post status was updated, update it with our rules
         * 
         * @param string $newStatus with the new post status
         * @param string $oldStatus with the old post status
         * @param object $post with the post object
         */
        public function transitionPostStatus($newStatus, $oldStatus, $post){
            self::updatePostStatus($post->ID);
        }
        
        
        /**
         * Based on the status and the configurations of the post, set the post_status accordingly
         * 
         * @param int $postId with the post identificator
         * @return string with the post status
         */
        public static function updatePostStatus($postId=0){
            // Get the ID from the parameter or from the main loop
	    $postId = absint( $postId );
	    if (!$postId):
		$postId = get_the_ID();
	    endif;
            
            // Get the full post object
            $post = get_post($postId);
            
            // Compute the new status
            $newStatus = false;
            switch(get_post_status($post)):
                case 'publish':
                case self::STATUS_online:
                case self::STATUS_scheduled:
                case self::STATUS_finished:
                    $numberOfClicks = PHP_INT_MAX; // @TODO get the number of clicks already in the system and stored in the database
                    if(self::hasClicksLimit($postId) && self::getClicksLimit($postId)>=$numberOfClicks || self::hasEndDate($postId) && self::getEndDate($postId)<=time()):
                        $newStatus = self::STATUS_finished;
                    elseif(self::hasStartDate() && self::getStartDate($postId)>=time()):
                        $newStatus = self::STATUS_scheduled;
                    else:
                        $newStatus = self::STATUS_online;
                    endif;
                break;
                case 'auto-draft':
                    $newStatus = self::STATUS_unavailable;
                break;
            endswitch;
            
            // Persist the new status
            if($newStatus):
                $oldStatus = get_post_status($post);
                $post->post_status = $newStatus;
                if($oldStatus!=$newStatus):
                    wp_update_post($post);
                    wp_transition_post_status($newStatus, $oldStatus, $post);
                endif;
            endif;
            
            return get_post_status($post);
        }
        
        /**
         * Set a custom value associated with a post
         * 
         * @param string $key with the key name
         * @param int $postId with the post identifier
         * @param string value with the value to associate with the key in the post
         */
        private static function setPostCustomValues($key, $value='', $postId=0){
            // Get the ID from the parameter or from the main loop
	    $postId = absint( $postId );
	    if (!$postId):
		$postId = get_the_ID();
	    endif;
            update_post_meta($postId, __CLASS__.$key, $value);
        }
        
        /**
         * Get a custom value associated with a post
         * 
         * @param string $key with the key name
         * @param int $postId with the post identifier
         * @return string value for the key or boolean false if the key was not found
         */
        private static function getPostCustomValues($key, $postId=0){
            // Get the ID from the parameter or from the main loop
	    $postId = absint( $postId );
	    if (!$postId):
		$postId = get_the_ID();
	    endif;
            $value = get_post_custom_values(__CLASS__.$key, $postId);
            return (!empty($value) && isset($value[0]))?$value[0]:false;
        }
        
        /**
         * Verify if the campaign has a cooling-off time limit
         * 
         * @param int $postId
         * @return boolean
         */
        public static function hasCoolOffLimit($postId=0){
            return (boolean)self::getPostCustomValues(self::$enableCoolOff, $postId);
        }
        
        /**
         * Get the cooling-off time between clicks on a specific campaign
         * 
         * @param int $postId
         * @return int with the cooling-off time
         */
        public static function getCoolOffLimit($postId=0){
            $limit = self::getPostCustomValues(self::$coolOff, $postId);
            return (int)(!self::hasCoolOffLimit($postId) || $limit===false?-1:$limit);
        }
        
        /**
         * Verify if the campaign is to be restricted by a cookie
         * 
         * @param int $postId
         * @return boolean
         */
        public static function isToRestrictByCookie($postId=0){
            return (boolean)(self::hasCoolOffLimit($postId) && self::getPostCustomValues(self::$restrictByCookie, $postId));
        }
        
        /**
         * Verify if the campaign is to be restricted by login (the user must be authenticated
         * 
         * @param int $postId
         * @return boolean
         */
        public static function isToRestrictByLogin($postId=0){
            return (boolean)(self::hasCoolOffLimit($postId) && self::getPostCustomValues(self::$restrictByLogin, $postId));
        }
        
        /**
         * Verify if the post has the click limits enforced
         * 
         * @param int $postId
         * @return boolean
         */
        public static function hasClicksLimit($postId=0){
            return (boolean)self::getPostCustomValues(self::$enableClickLimits, $postId);
        }
        
        /**
         * Get the maximum number of clicks allowed in a specific campaign
         * 
         * @param int $postId
         * @return int with the maximum number of clicks 
         */
        public static function getClicksLimit($postId=0){
            $limit = self::getPostCustomValues(self::$maxClicks, $postId);
            return (int)(!self::hasClicksLimit($postId) || $limit===false?-1:$limit);
        }
        
        /**
         * Verify if the post has a start date setting enabled
         * 
         * @param int $postId
         * @return boolean
         */
        public static function hasStartDate($postId=0){
            return (boolean)self::getPostCustomValues(self::$enableStartDate, $postId);
        }
        
        /**
         * Get the start date of a specific campaign
         * 
         * @param int $postId
         * @return int with timestamp of the start date
         */
        public static function getStartDate($postId=0){
            $date = self::getPostCustomValues(self::$startDate, $postId);
            return (int)(!self::hasStartDate($postId) || $date===false?gmmktime():$date);
        }
        
        /**
         * Verify if the post has a end date setting enabled
         * 
         * @param int $postId
         * @return boolean
         */
        public static function hasEndDate($postId=0){
            return (boolean)self::getPostCustomValues(self::$enableEndDate, $postId);
        }
        
        /**
         * Get the end date of a specific campaign
         * 
         * @param int $postId
         * @return int with timestamp of the end date
         */
        public static function getEndDate($postId=0){
            $date = self::getPostCustomValues(self::$endDate, $postId);
            // Default is set to current date plus a day
            return (int)(!self::hasEndDate($postId) || $date===false?gmmktime()+3600*24:$date);
        }
        
        /**
         * Install the database tables
         */
        public static function install() {

            // Load the libraries
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');

            // Load the plugin version
            $plugin = get_plugin_data(__FILE__);
            $version = $plugin['Version'];

            // Compare the plugin version with the local version, and update the database tables accordingly
            if (version_compare(get_option(self::DB_VERSION_FIELD_NAME), $version, '<')):

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
                /* // Wordpress doesn't enforce the InnoDB MySQL engine to use on their tables, so we cannot enable the foreign key constrainsts until the Wordpress requires the MySQL 5.5 as the minimum requirement
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
                /* // Wordpress doesn't enforce the InnoDB MySQL engine to use on their tables, so we cannot enable the foreign key constrainsts until the Wordpress requires the MySQL 5.5 as the minimum requirement
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
                if (!empty($contents)):
                    trigger_error($contents, E_USER_ERROR);
                endif;

                // Update the plugin DB version
                update_option(self::DB_VERSION_FIELD_NAME, $version);
            endif;
        }

        /**
         * Uninstall the plugin data
         */
        public static function uninstall() {
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

            $posts = get_posts(array(
                'post_type' => self::POST_TYPE,
                'posts_per_page' => -1,
                'nopaging' => true
                    ));

            foreach ($posts as $post):
                wp_delete_post($post->ID, true);
            endforeach;


            if (isset($wp_post_types[self::POST_TYPE])):
                unset($wp_post_types[self::POST_TYPE]);
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
        public static function getWpDB() {
            global $wpdb;

            return $wpdb;
        }

        /**
         * Register the plugin functions with the Wordpress hooks
         */
        public static function init() {
            $prefix = self::getWpDB()->prefix;
            // Append the Wordpress table prefix to the table names (if the prefix isn't already added)
            self::$tableClicks = (stripos(self::$tableClicks, $prefix) === 0 ? '' : $prefix) . self::$tableClicks;
            self::$tableSponsoredCampaigns = (stripos(self::$tableSponsoredCampaigns, $prefix) === 0 ? '' : $prefix) . self::$tableSponsoredCampaigns;

            // Register the install database method to be executed when the plugin is activated
            register_activation_hook(__FILE__, array(__CLASS__, 'install'));

            // Register the install database method to be executed when the plugin is updated
            add_action('plugins_loaded', array(__CLASS__, 'install'));

            // Register the remove database method when the plugin is removed
            register_uninstall_hook(__FILE__, array(__CLASS__, 'uninstall'));

            // Register the _init method to the Wordpress initialization action hook
            add_action('init', array(__CLASS__, '_init'));

            // Register the addMetaBox method to the Wordpress backoffice administration initialization action hook
            add_action('admin_init', array(__CLASS__, 'addMetaBox'));

            // Register the savePost method to the Wordpress save_post action hook
            add_action('save_post', array(__CLASS__, 'savePost'));

            // Add thePosts method to filter the_posts
            add_filter('the_posts', array(__CLASS__, 'thePosts'), 10, 2);

            // Add mapMetaCapabilities method to filter map_meta_cap  // Maybe we can use this in the module 2
            //add_filter('map_meta_cap', array(__CLASS__, 'mapMetaCapabilities'), 10, 4);

            // Register the adminEnqueueScripts method to the Wordpress admin_enqueue_scripts action hook
            add_action('admin_enqueue_scripts', array(__CLASS__, 'adminEnqueueScripts'));

            // Register the adminPrintStyles method to the Wordpress admin_print_styles action hook
            add_action('admin_print_styles', array(__CLASS__, 'adminPrintStyles'));

            // Register the adminPrintStyles method to the Wordpress transition_post_status action hook
            add_action('transition_post_status', array(__CLASS__, 'transitionPostStatus'), 10, 3);
            
            
        }

    }

    endif;

ClickToDonate::init();