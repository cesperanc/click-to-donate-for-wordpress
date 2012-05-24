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

                // Register the adminEnqueueScripts method to the Wordpress admin_enqueue_scripts action hook
                add_action('admin_enqueue_scripts', array(__CLASS__, 'adminEnqueueScripts'));

                // Register the adminPrintStyles method to the Wordpress admin_print_styles action hook
                //add_action('admin_print_styles', array(__CLASS__, 'adminPrintStyles'));
                
                add_action('wp_ajax_' . 'ctd_get_visits', array(__CLASS__, 'getBannerVisits'));
            endif;
        }
        
        /**
         * Register the scripts to be loaded on the backoffice, on our custom post type
         */
        public function adminEnqueueScripts() {
            if (is_admin()):
                $suffix = ClickToDonateView::debugSufix();
            
                if(($current_screen = get_current_screen()) && $current_screen->post_type == ClickToDonateController::POST_TYPE):
                    // Register the scripts
                    wp_enqueue_script('google-jsapi', 'https://www.google.com/jsapi');
                    
                    wp_enqueue_script('jquery');
                    // Admin script
                    //wp_enqueue_script(ClickToDonate::CLASS_NAME.'_post_graph', plugins_url("js/ctd-graph$suffix.js", ClickToDonate::FILE), array('jquery', 'google-jsapi'), '1.0');
                endif;
            endif;
        }
        
        /**
         * Register the styles to be loaded on the backoffice on our custom post type
         */
        public function adminPrintStyles() {
            if (is_admin()):
                $suffix = ClickToDonateView::debugSufix();
                if(($current_screen = get_current_screen()) && $current_screen->post_type == ClickToDonateController::POST_TYPE):
                    wp_enqueue_style(ClickToDonate::CLASS_NAME . '_jquery-ui-theme', plugins_url("css/jquery-ui/jquery-ui-1.8.20.custom$suffix.css", ClickToDonate::FILE), array(), '1.8.20');
                    wp_enqueue_style(ClickToDonate::CLASS_NAME . '_ui-spinner', plugins_url("css/ui-spinner/ui-spinner$suffix.css", ClickToDonate::FILE), array(), '1.20');
                    wp_enqueue_style(ClickToDonate::CLASS_NAME . '_admin', plugins_url("css/admin$suffix.css", ClickToDonate::FILE), array(ClickToDonate::CLASS_NAME . '_ui-spinner', ClickToDonate::CLASS_NAME . '_jquery-ui-theme'), '1.0');
                endif;
                
                wp_enqueue_style('ctd-tinymce', plugins_url("css/tinymce/tinymce$suffix.css", ClickToDonate::FILE), array(), '1.0');
            endif;
        }

        /**
         * Add a metabox to the campaign post type
         */
        public function addMetaBox() {
            // Replace the submit core metabox by ours
            add_meta_box(__CLASS__, __('Campaign views', 'ClickToDonate'), array(__CLASS__, 'writeMetaBox'), ClickToDonateController::POST_TYPE);
        }

        /**
         * Output a custom metabox for saving the post
         * @param Object $post 
         */
        public static function writeMetaBox($post) {
            ?>
                <div style="overflow: hidden;">
                    <div id="ctd-chart-container" style='height: 300px;'></div>
                </div>
                <script>
                    google.load("visualization", "1", {packages:["corechart"], 'language': '<?php echo(get_bloginfo('language')); ?>'});
                    var $j = jQuery.noConflict();
                    $j(document).ready(function(){               
                        // Get TIER1Tickets                 
                        $j("#ctd-chart-container").addClass("loading").html('<?php echo(esc_js( __( 'Loading...', 'ClickToDonate' ) )); ?>');

                        $j.post( 
                            ajaxurl, {
                                'action' : 'ctd_get_visits',
                                'postId' : '<?php echo(esc_js(ClickToDonateController::getPostID($post))); ?>',
                                '_ajax_ctd_get_visits_nonce' : '<?php echo(esc_js(wp_create_nonce('ctd-get-visits'))); ?>'
                            }, function(data) {
                                google.setOnLoadCallback(drawChart(data));
                            }, "json" 
                        );
                    }); 
                    //google.setOnLoadCallback(drawChart);
                    function drawChart(rows) {
                        var data = new google.visualization.DataTable();
                        data.addColumn('string', '<?php echo(esc_js( __( 'Day', 'ClickToDonate' ) )); ?>');
                        data.addColumn('number', '<?php echo(esc_js( __( 'Total visits', 'ClickToDonate' ) )); ?>');
//                        for (var row in rows){
//                            rows[row][0] = new Date(rows[row][0]);
//                        }
                        data.addRows(rows);
                        /*var data = google.visualization.arrayToDataTable(data/*[
                        ['Year', 'Sales', 'Expenses'],
                        ['2004',  1000,      400],
                        ['2005',  1170,      460],
                        ['2006',  660,       1120],
                        ['2007',  1030,      540]
                        ]*///);

                        var options = {
                            hAxis: {title: '<?php echo(esc_js( __( 'Days', 'ClickToDonate' ) )); ?>', titleTextStyle: {color: 'red'}}/*,
                            isStacked: true*/
                        };

                        var chart = new google.visualization.ColumnChart(document.getElementById('ctd-chart-container'));
                        chart.draw(data, options);
                        
                        $j("#ctd-chart-container").removeClass("loading")
                    }
                    //https://developers.google.com/chart/interactive/docs/gallery/controls#using_controls_and_dashboards
                    //https://developers.google.com/chart/interactive/docs/customizing_axes?hl=pt-PT#Help
                </script>
            <?php
        }
        
        
        
        /**
         * Send the campaigns list as a response of an ajax request 
         */
        public function getBannerVisits() {
            check_ajax_referer('ctd-get-visits', '_ajax_ctd_get_visits_nonce');
            
            $postId = !empty($_POST['postId']) ? absint($_POST['postId']) : 0;
            
            $results = ClickToDonateController::getBannerVisitsPerDay($postId);

            if (!isset($results))
                die('0');
            
            $resultsArray = array();
            foreach ($results as $result):
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