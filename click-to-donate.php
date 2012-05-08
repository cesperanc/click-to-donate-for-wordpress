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
        const STATUS_published = 'publish';
        const STATUS_scheduled = 'ctd-scheduled';
        const STATUS_finished = 'ctd-finished';
        const STATUS_draft = 'draft';
        const STATUS_trash = 'trash';

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
                'capability_type' => self::POST_TYPE,
                'capabilities' => array(
                    'publish_posts' => 'publish_' . self::POST_TYPE,
                    'edit_posts' => 'edit_pages',
                    'edit_others_posts' => 'edit_others_pages',
                    'delete_posts' => 'delete_pages',
                    'delete_others_posts' => 'delete_others_pages',
                    'read_private_posts' => 'read_private_pages',
                    'edit_post' => 'edit_pages',
                    'delete_post' => 'delete_pages',
                    'read_post' => 'read_pages'
                )
                    )
            );
            /*
              if(!post_type_exists(self::STATUS_published)):
              register_post_status( self::STATUS_published, array(
              'label' => __( 'Published', __CLASS__ ),
              'public' => true,
              'exclude_from_search' => true,
              'show_in_admin_all_list' => true,
              'show_in_admin_status_list' => true,
              'label_count' => _n_noop( 'Unread <span class="count">(%s)</span>', 'Unread <span class="count">(%s)</span>' ),
              ) );
              endif;

              if(!post_type_exists(self::STATUS_published)):
              register_post_status( 'unread', array(
              'label' => __( 'Unread', __CLASS__ ),
              'public' => true,
              'exclude_from_search' => false,
              'show_in_admin_all_list' => true,
              'show_in_admin_status_list' => true,
              'label_count' => _n_noop( 'Unread <span class="count">(%s)</span>', 'Unread <span class="count">(%s)</span>' ),
              ) );
              endif;
             */
        }

        public function mapMetaCapabilities($caps, $cap, $userId, $args) {
            // If we are checking for the publish capability on our post type, return empty capabities to block the content publication (we will be using our own mechanism)
            if ('publish_' . self::POST_TYPE == $cap):
                return array('do_not_allow');
            endif;

            return $caps;
        }

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
            if (is_admin() && ($current_screen = get_current_screen()) && $current_screen->post_type == self::POST_TYPE):
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
                            <?php submit_button(__('Save'), 'button', 'save'); ?>
                        </div>

                        <div id="minor-publishing-actions">
                            <div id="preview-action">
                                <?php
                                if ('publish' == $post->post_status) {
                                    $preview_link = esc_url(get_permalink($post->ID));
                                    $preview_button = __('Preview Changes');
                                } else {
                                    $preview_link = get_permalink($post->ID);
                                    if (is_ssl())
                                        $preview_link = str_replace('http://', 'https://', $preview_link);
                                    $preview_link = esc_url(apply_filters('preview_post_link', add_query_arg('preview', 'true', $preview_link)));
                                    $preview_button = __('Preview');
                                }
                                ?>
                                <a class="preview button" href="<?php echo $preview_link; ?>" target="wp-preview" id="post-preview" tabindex="4"><?php echo $preview_button; ?></a>
                                <input type="hidden" name="wp-preview" id="wp-preview" value="" />
                            </div>

                            <div class="clear"></div>
                        </div><?php // /minor-publishing-actions  ?>

                        <?php 
                            // campaign configuration

                            $views = get_post_custom_values(__CLASS__ . '_views', $post->ID);
                            if (!empty($views) && isset($views[0])):
                                $views = $views[0];
                            endif;

                            $enableClicksLimit = get_post_custom_values(__CLASS__ . '_enable_click_limits', $post->ID);
                            $maxClicks = get_post_custom_values(__CLASS__ . '_maxClicks', $post->ID);

                            $enableStartDate = get_post_custom_values(__CLASS__ . '_enable_startDate', $post->ID);
                            $startDate = get_post_custom_values(__CLASS__ . '_startDate', $post->ID);

                            $enableEndDate = get_post_custom_values(__CLASS__ . '_enable_endDate', $post->ID);
                            $endDate = get_post_custom_values(__CLASS__ . '_endDate', $post->ID);
                            
                            // Extract the hours from the timestamp
                            if(empty($startDate) || empty($startDate[0])):
                                $startHours = array('0');
                            else:
                                $startHours = array(date('G', $startDate[0]));
                            endif;
                            if(empty($startDate) || empty($startDate[0])):
                                $startMinutes = array('00');
                            else:
                                $startMinutes = array(date('i', $startDate[0]));
                            endif;
                            
                            // Extract the minutes from the timestamp
                            if(empty($endDate) || empty($endDate[0])):
                                $endHours = array('0');
                            else:
                                $endHours = array(date('G', $endDate[0]));
                            endif;
                            if(empty($endDate) || empty($endDate[0])):
                                $endMinutes = array('00');
                            else:
                                $endMinutes = array(date('i', $endDate[0]));
                            endif;
                            
                        ?>
                        <div id="ctd-campaign-admin" class="hide-if-no-js misc-pub-section">
                            <fieldset id="ctd-enable-maxclicks-container" class="ctd-enable-container">
                                <legend><input id="ctd-enable-maxclicks" name="<?php echo(__CLASS__ . '_enable_click_limits'); ?>" value="enable_click_limits"<?php checked('enable_click_limits', $enableClicksLimit[0]); ?> type="checkbox"/><label class="selectit" for="ctd-enable-maxclicks"><?php _e('Limit the number of clicks', __CLASS__); ?></label></legend>
                                <div id="ctd-maxclicks-container" class="start-hidden">
                                    <label class="selectit"><?php _e('Clicks limit:', __CLASS__); ?> <input title="<?php esc_attr_e('Specify the number of clicks allowed before disabling the campaign', __CLASS__) ?>" id="ctd-maximum-clicks-limit" type="text" name="<?php echo(__CLASS__ . '_maxClicks'); ?>" value="<?php echo($maxClicks[0]); ?>" /></label>
                                </div>

                            </fieldset>

                            <fieldset id="ctd-enable-startdate-container" class="ctd-enable-container">
                                <legend>
                                    <input id="ctd-enable-startdate" name="<?php echo(__CLASS__ . '_enable_startDate'); ?>" value="enable_startDate"<?php checked('enable_startDate', $enableStartDate[0]); ?> type="checkbox"/><label class="selectit" for="ctd-enable-startdate"><?php _e('Set the campaign start date', __CLASS__); ?></label>
                                </legend>
                                <div id="ctd-startdate-container" class="start-hidden">
                                    <label class="selectit"><?php _e('Start date:', __CLASS__); ?> <input style="width: 6em;" size="8" maxlength="10" title="<?php esc_attr_e('Specify the start date when the campaign is supposed to start', __CLASS__) ?>" id="ctd-startdate" type="text" /></label>
                                    <input id="ctd-hidden-startdate" type="hidden" name="<?php echo(__CLASS__ . '_startDate'); ?>" value="<?php echo($startDate[0]); ?>" />
                                    @<input title="<?php esc_attr_e('Specify the campaign starting hours', __CLASS__) ?>" style="width: 2em;" size="2" maxlength="2" id="ctd-starthours" name="<?php echo(__CLASS__ . '_startHours'); ?>" type="text" value="<?php echo($startHours[0]); ?>" />:<input title="<?php esc_attr_e('Specify the campaign starting minutes', __CLASS__) ?>" style="width: 2em;" size="2" maxlength="2" id="ctd-startminutes" name="<?php echo(__CLASS__ . '_startMinutes'); ?>" type="text" value="<?php echo($startMinutes[0]); ?>" />
                                </div>
                            </fieldset>

                            <fieldset id="ctd-enable-enddate-container" class="ctd-enable-container">
                                <legend>
                                    <input id="ctd-enable-enddate" name="<?php echo(__CLASS__ . '_enable_endDate'); ?>" value="enable_endDate"<?php checked('enable_endDate', $enableEndDate[0]); ?> type="checkbox"/><label class="selectit" for="ctd-enable-enddate"><?php _e('Set the campaign end date', __CLASS__); ?></label>
                                </legend>
                                <div id="ctd-enddate-container" class="start-hidden">
                                    <label class="selectit"><?php _e('End date:', __CLASS__); ?> <input style="width: 6em;" size="8" maxlength="10" title="<?php esc_attr_e('Specify the end date when the campaign is supposed to end', __CLASS__) ?>" id="ctd-enddate" type="text" name="<?php echo(__CLASS__ . '_endDate'); ?>" /></label>
                                    <input id="ctd-hidden-enddate" type="hidden" name="<?php echo(__CLASS__ . '_endDate'); ?>" value="<?php echo($endDate[0]); ?>" />
                                    @<input title="<?php esc_attr_e('Specify the campaign ending hours', __CLASS__) ?>" style="width: 2em;" size="2" maxlength="2" id="ctd-endhours" name="<?php echo(__CLASS__ . '_endHours'); ?>" type="text" value="<?php echo($endHours[0]); ?>" />:<input title="<?php esc_attr_e('Specify the campaign ending minutes', __CLASS__) ?>" style="width: 2em;" size="2" maxlength="2" id="ctd-endminutes" name="<?php echo(__CLASS__ . '_endMinutes'); ?>" type="text" value="<?php echo($endMinutes[0]); ?>" />
                                </div>
                            </fieldset>

                            <div>
                                <?php printf(__('Views: %s', __CLASS__), $views); ?>
                            </div>
                        </div>
                        
                        <div id="misc-publishing-actions">

                            <div class="misc-pub-section<?php if (!$can_publish) { echo ' misc-pub-section-last'; } ?>"><label for="post_status"><?php _e('Status:') ?></label>
                                <span id="post-status-display">
                                    <?php
                                    // @TODO: implement the custom states for the campaigns
                                    switch ($post->post_status) {
                                        case 'private':
                                            _e('Privately Published');
                                            break;
                                        case 'publish':
                                            _e('Published');
                                            break;
                                        case 'future':
                                            _e('Scheduled');
                                            break;
                                        case 'pending':
                                            _e('Pending Review');
                                            break;
                                        case 'draft':
                                        case 'auto-draft':
                                            _e('Draft');
                                            break;
                                    }
                                    ?>
                                </span>
                                <?php if ('publish' == $post->post_status || 'private' == $post->post_status || $can_publish) { ?>
                                    <a href="#post_status" <?php if ('private' == $post->post_status) { ?>style="display:none;" <?php } ?>class="edit-post-status hide-if-no-js" tabindex='4'><?php _e('Edit') ?></a>

                                    <div id="post-status-select" class="hide-if-js">
                                        <input type="hidden" name="hidden_post_status" id="hidden_post_status" value="<?php echo esc_attr(('auto-draft' == $post->post_status ) ? 'draft' : $post->post_status); ?>" />
                                        <select name='post_status' id='post_status' tabindex='4'>
                                            <?php if ('publish' == $post->post_status) : ?>
                                                <option<?php selected($post->post_status, 'publish'); ?> value='publish'><?php _e('Published') ?></option>
                                            <?php elseif ('private' == $post->post_status) : ?>
                                                <option<?php selected($post->post_status, 'private'); ?> value='publish'><?php _e('Privately Published') ?></option>
                                            <?php elseif ('future' == $post->post_status) : ?>
                                                <option<?php selected($post->post_status, 'future'); ?> value='future'><?php _e('Scheduled') ?></option>
                                            <?php endif; ?>
                                            <option<?php selected($post->post_status, 'pending'); ?> value='pending'><?php _e('Pending Review') ?></option>
                                            <?php if ('auto-draft' == $post->post_status) : ?>
                                                <option<?php selected($post->post_status, 'auto-draft'); ?> value='draft'><?php _e('Draft') ?></option>
                                            <?php else : ?>
                                                <option<?php selected($post->post_status, 'draft'); ?> value='draft'><?php _e('Draft') ?></option>
                                            <?php endif; ?>
                                        </select>
                                        <a href="#post_status" class="save-post-status hide-if-no-js button"><?php _e('OK'); ?></a>
                                        <a href="#post_status" class="cancel-post-status hide-if-no-js"><?php _e('Cancel'); ?></a>
                                    </div>
                                <?php } ?>
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
                                    $delete_text = __('Delete Permanently');
                                else
                                    $delete_text = __('Move to Trash');
                                ?>
                                <a class="submitdelete deletion" href="<?php echo get_delete_post_link($post->ID); ?>"><?php echo $delete_text; ?></a><?php }
                ?>
                        </div>

                        <div id="publishing-action">
                            <img src="<?php echo esc_url(admin_url('images/wpspin_light.gif')); ?>" class="ajax-loading" id="ajax-loading" alt="" />
                            <?php
                            if (!in_array($post->post_status, array('publish', 'future', 'private')) || 0 == $post->ID) {
                                if ($can_publish) :
                                    if (!empty($post->post_date_gmt) && time() < strtotime($post->post_date_gmt . ' +0000')) :
                                        ?>
                                        <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Schedule') ?>" />
                                        <?php submit_button(__('Schedule'), 'primary', 'publish', false, array('tabindex' => '5', 'accesskey' => 'p')); ?>
                                    <?php else : ?>
                                        <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Publish') ?>" />
                                        <?php submit_button(__('Publish'), 'primary', 'publish', false, array('tabindex' => '5', 'accesskey' => 'p')); ?>
                                    <?php endif;
                                else :
                                    ?>
                                    <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Submit for Review') ?>" />
                        <?php submit_button(__('Submit for Review'), 'primary', 'publish', false, array('tabindex' => '5', 'accesskey' => 'p')); ?>
                                <?php
                                endif;
                            } else {
                                ?>
                                <input name="original_publish" type="hidden" id="original_publish" value="<?php esc_attr_e('Update') ?>" />
                                <input name="save" type="submit" class="button-primary" id="publish" tabindex="5" accesskey="p" value="<?php esc_attr_e('Update') ?>" />
                    <?php }
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
                    // Get the posted data
                    if (isset($_POST[__CLASS__ . '_enable_click_limits'])):
                        $enableClickLimits = $_POST[__CLASS__ . '_enable_click_limits'];
                        $maxClicks = isset($_POST[__CLASS__ . '_maxClicks']) ? $_POST[__CLASS__ . '_maxClicks'] : -1;
                    else:
                        $enableClickLimits = false;
                        $maxClicks = -1;
                    endif;

                    if (isset($_POST[__CLASS__ . '_enable_startDate'])):
                        $enableStartDate = $_POST[__CLASS__ . '_enable_startDate'];
                        $startDate = isset($_POST[__CLASS__ . '_startDate']) ? $_POST[__CLASS__ . '_startDate'] : '';
                        
                        error_log(date("Y-m-d H:i:s",$startDate));
                        
                        $hours = isset($_POST[__CLASS__ . '_startHours']) ? (int)$_POST[__CLASS__ . '_startHours'] : 0;
                        $minutes = isset($_POST[__CLASS__ . '_startMinutes']) ? (int)$_POST[__CLASS__ . '_startMinutes'] : 0;
                        $startDate = mktime ($hours, $minutes, 0, date('n', $startDate), date('j', $startDate), date('Y', $startDate));
                    else:
                        $enableStartDate = false;
                        $startDate = '';
                    endif;
                    

                    if (isset($_POST[__CLASS__ . '_enable_endDate'])):
                        $enableEndDate = $_POST[__CLASS__ . '_enable_endDate'];
                        $endDate = isset($_POST[__CLASS__ . '_endDate']) ? $_POST[__CLASS__ . '_endDate'] : '';
                        $hours = isset($_POST[__CLASS__ . '_endHours']) ? (int)$_POST[__CLASS__ . '_endHours'] : 0;
                        $minutes = isset($_POST[__CLASS__ . '_endMinutes']) ? (int)$_POST[__CLASS__ . '_endMinutes'] : 0;
                        $endDate = mktime ($hours, $minutes, 0, date('n', $endDate), date('j', $endDate), date('Y', $endDate));
                    else:
                        $enableEndDate = false;
                        $endDate = '';
                    endif;
                    
                    if(is_numeric($startDate) && is_numeric($endDate) && $startDate<$endDate):
                        $t = $startDate;
                        $startDate = $endDate;
                        $endDate = $t;
                    endif;

                    // Save the object in the database
                    update_post_meta($postId, __CLASS__ . '_enable_click_limits', $enableClickLimits);
                    update_post_meta($postId, __CLASS__ . '_maxClicks', $maxClicks);
                    update_post_meta($postId, __CLASS__ . '_enable_startDate', $enableStartDate);
                    update_post_meta($postId, __CLASS__ . '_startDate', $startDate);
                    update_post_meta($postId, __CLASS__ . '_enable_endDate', $enableEndDate);
                    update_post_meta($postId, __CLASS__ . '_endDate', $endDate);

                    error_log($_POST[__CLASS__ . '_enable_click_limits']);

                    break;
            endswitch;
            return $postId;
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

            // Add mapMetaCapabilities method to filter map_meta_cap
            add_filter('map_meta_cap', array(__CLASS__, 'mapMetaCapabilities'), 10, 4);

            // Register the adminEnqueueScripts method to the Wordpress admin_enqueue_scripts action hook
            add_action('admin_enqueue_scripts', array(__CLASS__, 'adminEnqueueScripts'));

            // Register the adminPrintStyles method to the Wordpress admin_print_styles action hook
            add_action('admin_print_styles', array(__CLASS__, 'adminPrintStyles'));
        }

    }

    endif;

ClickToDonate::init();