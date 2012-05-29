<?php
/**
 * Provides the view functionality for the plugin 
 */

if (!class_exists('ClickToDonateGraphView')):
    class ClickToDonateGraphView {
        
        public static function init(){
            
            if (is_admin()):
                // Register the addMetaBox method to the Wordpress backoffice administration initialization action hook
                add_action('admin_init', array(__CLASS__, 'addMetaBox'));
            
                // Register the wpDashboardSetup method to the wp_dashboard_setup action hook
                add_action('wp_dashboard_setup', array(__CLASS__, 'wpDashboardSetup'));

                // Register the adminEnqueueScripts method to the Wordpress admin_enqueue_scripts action hook
                add_action('admin_enqueue_scripts', array(__CLASS__, 'adminEnqueueScripts'));

                // Register the adminPrintStyles method to the Wordpress admin_print_styles action hook
                add_action('admin_print_styles', array(__CLASS__, 'adminPrintStyles'));
                
                add_action('wp_ajax_' . 'ctd_get_visits', array(__CLASS__, 'getBannerVisits'));
            endif;
        }
        
        /**
         * Register the scripts to be loaded on the backoffice, on our custom post type
         */
        public function adminEnqueueScripts() {
            if (is_admin()):
                $suffix = ClickToDonateView::debugSufix();
            
                $current_screen = get_current_screen();
                if(is_blog_admin() && current_user_can('publish_posts') && ($current_screen->id=='dashboard' || $current_screen->post_type == ClickToDonateController::POST_TYPE)):
                    // Register the scripts
                    wp_enqueue_script('google-jsapi', 'https://www.google.com/jsapi');
                    
                    // Admin script
                    wp_enqueue_script(ClickToDonate::CLASS_NAME.'_common', plugins_url("js/common$suffix.js", ClickToDonate::FILE), array('jquery', 'jquery-ui-datepicker'), '1.0');
                    wp_enqueue_script(ClickToDonate::CLASS_NAME.'_post_graph', plugins_url("js/ctd-graph$suffix.js", ClickToDonate::FILE), array(ClickToDonate::CLASS_NAME.'_common', 'jquery', 'google-jsapi', 'jquery-ui-datepicker'), '1.0');
                    wp_localize_script(ClickToDonate::CLASS_NAME . '_post_graph', 'ctdGraphL10n', array(
                        'language' => esc_js(esc_js(get_bloginfo('language'))),
                        'loading' => esc_js(__( 'Loading...', 'ClickToDonate' )),
                        'day' => esc_js(__( 'Day', 'ClickToDonate' )),
                        'days' => esc_js(__( 'Days', 'ClickToDonate' )),
                        'totalVisits' => esc_js(__('Total visits', 'ClickToDonate')),
                        'privateMethodDoesNotExist' => __('Private method {0} does not exist', 'ClickToDonate'),
                        'methodDoesNotExist' => __('Method {0} does not exist', 'ClickToDonate'),
                        'closeText' => __('Done', 'ClickToDonate'),
                        'currentText' => __('Today', 'ClickToDonate'),
                        'dateFormat' => __('mm/dd/yy', 'ClickToDonate'),
                        'dayNamesSunday' => __('Sunday', 'ClickToDonate'),
                        'dayNamesMonday' => __('Monday', 'ClickToDonate'),
                        'dayNamesTuesday' => __('Tuesday', 'ClickToDonate'),
                        'dayNamesWednesday' => __('Wednesday', 'ClickToDonate'),
                        'dayNamesThursday' => __('Thursday', 'ClickToDonate'),
                        'dayNamesFriday' => __('Friday', 'ClickToDonate'),
                        'dayNamesSaturday' => __('Saturday', 'ClickToDonate'),
                        'dayNamesMinSu' => __('Su', 'ClickToDonate'),
                        'dayNamesMinMo' => __('Mo', 'ClickToDonate'),
                        'dayNamesMinTu' => __('Tu', 'ClickToDonate'),
                        'dayNamesMinWe' => __('We', 'ClickToDonate'),
                        'dayNamesMinTh' => __('Th', 'ClickToDonate'),
                        'dayNamesMinFr' => __('Fr', 'ClickToDonate'),
                        'dayNamesMinSa' => __('Sa', 'ClickToDonate'),
                        'dayNamesShortSun' => __('Sun', 'ClickToDonate'),
                        'dayNamesShortMon' => __('Mon', 'ClickToDonate'),
                        'dayNamesShortTue' => __('Tue', 'ClickToDonate'),
                        'dayNamesShortWed' => __('Wed', 'ClickToDonate'),
                        'dayNamesShortThu' => __('Thu', 'ClickToDonate'),
                        'dayNamesShortFri' => __('Fri', 'ClickToDonate'),
                        'dayNamesShortSat' => __('Sat', 'ClickToDonate'),
                        'monthNamesJanuary' => __('January', 'ClickToDonate'),
                        'monthNamesFebruary' => __('February', 'ClickToDonate'),
                        'monthNamesMarch' => __('March', 'ClickToDonate'),
                        'monthNamesApril' => __('April', 'ClickToDonate'),
                        'monthNamesMay' => __('May', 'ClickToDonate'),
                        'monthNamesJune' => __('June', 'ClickToDonate'),
                        'monthNamesJuly' => __('July', 'ClickToDonate'),
                        'monthNamesAugust' => __('August', 'ClickToDonate'),
                        'monthNamesSeptember' => __('September', 'ClickToDonate'),
                        'monthNamesOctober' => __('October', 'ClickToDonate'),
                        'monthNamesNovember' => __('November', 'ClickToDonate'),
                        'monthNamesDecember' => __('December', 'ClickToDonate'),
                        'monthNamesShortJan' => __('Jan', 'ClickToDonate'),
                        'monthNamesShortFeb' => __('Feb', 'ClickToDonate'),
                        'monthNamesShortMar' => __('Mar', 'ClickToDonate'),
                        'monthNamesShortApr' => __('Apr', 'ClickToDonate'),
                        'monthNamesShortMay' => __('May', 'ClickToDonate'),
                        'monthNamesShortJun' => __('Jun', 'ClickToDonate'),
                        'monthNamesShortJul' => __('Jul', 'ClickToDonate'),
                        'monthNamesShortAug' => __('Aug', 'ClickToDonate'),
                        'monthNamesShortSep' => __('Sep', 'ClickToDonate'),
                        'monthNamesShortOct' => __('Oct', 'ClickToDonate'),
                        'monthNamesShortNov' => __('Nov', 'ClickToDonate'),
                        'monthNamesShortDec' => __('Dec', 'ClickToDonate'),
                        'nextText' => __('Next', 'ClickToDonate'),
                        'prevText' => __('Prev', 'ClickToDonate'),
                        'weekHeader' => __('Wk', 'ClickToDonate')
                    ));
                endif;
            endif;
        }
        
        /**
         * Register the styles to be loaded on the backoffice on our custom post type
         */
        public function adminPrintStyles() {
            if (is_admin()):
                $suffix = ClickToDonateView::debugSufix();
            
                $current_screen = get_current_screen();
                if(is_blog_admin() && current_user_can('publish_posts') && ($current_screen->id=='dashboard' || $current_screen->post_type == ClickToDonateController::POST_TYPE)):
                    wp_enqueue_style(ClickToDonate::CLASS_NAME . '_jquery-ui-theme', plugins_url("css/jquery-ui/jquery-ui-1.8.20.custom$suffix.css", ClickToDonate::FILE), array(), '1.8.20');
                    wp_enqueue_style(ClickToDonate::CLASS_NAME . '_common', plugins_url("css/common$suffix.css", ClickToDonate::FILE), array(ClickToDonate::CLASS_NAME . '_jquery-ui-theme'), '1.0');
                endif;
            endif;
        }

        /**
         * Add a metabox to dashboard
         */
        public function wpDashboardSetup() {
            // Add our metabox with the graphics to the dashboard
            if(is_blog_admin() && current_user_can('publish_posts')):
                wp_add_dashboard_widget(__CLASS__, __('Campaigns views', 'ClickToDonate'), array(__CLASS__, 'writeMetaBox'));
            endif;
        }

        /**
         * Add a metabox to the campaign post type
         */
        public function addMetaBox() {
            // Add our metabox with the graphics to our custom post type
            add_meta_box(__CLASS__, __('Campaign views', 'ClickToDonate'), array(__CLASS__, 'writeMetaBox'), ClickToDonateController::POST_TYPE);
        }

        /**
         * Output a custom metabox for saving the post
         * @param Object $post 
         */
        public static function writeMetaBox($post) {
            ?>
                <div style="margin: 10px 0 20px;">
                    <label class="selectit"><?php _e('Period start date:', 'ClickToDonate'); ?> <input style="width: 6em;" size="8" maxlength="10" title="<?php esc_attr_e('Specify the period start date', 'ClickToDonate') ?>" id="ctd-graph-startdate" type="text" /></label>
                    <input id="ctd-hidden-graph-startdate" type="hidden" value="<?php echo(((current_time('timestamp')-3600*24*7)*1000)); ?>" />
                    <label class="selectit"><?php _e('Period end date:', 'ClickToDonate'); ?> <input style="width: 6em;" size="8" maxlength="10" title="<?php esc_attr_e('Specify the period end date', 'ClickToDonate') ?>" id="ctd-graph-enddate" type="text" /></label>
                    <label class="selectit"><?php _e('Period granularity:', 'ClickToDonate'); ?> <input id="ctd-hidden-graph-enddate" type="hidden" value="<?php echo((current_time('timestamp')*1000)); ?>" /></label>
                    
                    <select id="ctd-graph-date-granularity">
                        <option selected="selected" value='<?php echo(esc_attr(ClickToDonateModel::DATE_GRANULARITY_DAYS)); ?>'><?php _e('Days', 'ClickToDonate') ?></option>
                        <option value='<?php echo(esc_attr(ClickToDonateModel::DATE_GRANULARITY_MONTHS)); ?>'><?php _e('Months', 'ClickToDonate') ?></option>
                        <option value='<?php echo(esc_attr(ClickToDonateModel::DATE_GRANULARITY_YEARS)); ?>'><?php _e('Years', 'ClickToDonate') ?></option>
                    </select>
                    
                    <a class="button" id="ctd-load-graph"><?php _e('Load', 'ClickToDonate'); ?></a>
                </div>
                <div id="ctd-chart-container" style='width: 100%; height: 300px;'></div>
                <script>
                    google.load("visualization", "1", {
                        packages:["corechart"], 
                        'language': ctdGraphL10n.language
                    });
                    
                    $j('#ctd-load-graph').click(function(){
                        var $j = jQuery.noConflict();
                        $j('#ctd-chart-container').ctdGraph(
                            'loadData',{
                                'action' : 'ctd_get_visits',
                                '_ajax_ctd_get_visits_nonce' : '<?php echo(esc_attr(wp_create_nonce('ctd-get-visits'))); ?>',
                                'startDate': ($j("#ctd-hidden-graph-startdate").val()/1000),
                                'endDate': ($j("#ctd-hidden-graph-enddate").val()/1000),
                                'dateGranularity': ($j("#ctd-graph-date-granularity").val())
                                <?php if(isset($post)): echo(", 'postId' : '".esc_js(ClickToDonateController::getPostID($post))."'"); endif; ?>
                            }
                        );
                        return false;
                    });
                </script>
            <?php
        }
        
        
        
        /**
         * Send the campaigns list as a response of an ajax request 
         */
        public function getBannerVisits() {
            check_ajax_referer('ctd-get-visits', '_ajax_ctd_get_visits_nonce');
            
            $postId = !empty($_POST['postId']) ? absint($_POST['postId']) : 0;
            
            $startDate = !empty($_POST['startDate']) ? absint($_POST['startDate']) : 0;
            $endDate = !empty($_POST['endDate']) ? absint($_POST['endDate']) : 0;
            $dateGranularity = (!empty($_POST['dateGranularity']) && in_array($_POST['dateGranularity'], array(
                ClickToDonateModel::DATE_GRANULARITY_DAYS, 
                ClickToDonateModel::DATE_GRANULARITY_MONTHS, 
                ClickToDonateModel::DATE_GRANULARITY_YEARS))) ? $_POST['dateGranularity'] : ClickToDonateModel::DATE_GRANULARITY_DAYS;
            
            $results = ClickToDonateController::getBannerVisitsPerDay($postId, 0, $startDate, $endDate, $dateGranularity);

            if (!isset($results))
                die('0');
            
            $resultsArray = array();
            foreach ($results as $result):
                if(empty($resultsArray)):
                    $keys = array();
                    foreach ($result as $key=>$value):
                        $keys[] = $key;
                    endforeach;
                    $resultsArray[] = $keys;
                endif;
                $values = array();
                foreach ($result as $key=>$value):
                    $values[] = empty($values)?"{$value}":(int)$value;
                endforeach;
                $resultsArray[] = $values;
            endforeach;

            echo json_encode($resultsArray);
            echo "\n";

            exit;
        }
    }
endif;
ClickToDonateGraphView::init();