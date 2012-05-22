<?php
/**
 * Provides the model functionality for the plugin 
 */

if (!class_exists('ClickToDonateModel')):
    class ClickToDonateModel {
        
        /**
         * The database variable name to store the plugin database version
         */
        const DB_VERSION_FIELD_NAME = 'ClickToDonateDbVersion';
        
        // Table variables name
        private static $tableClicks = 'clicks';
        private static $tableClicksID = 'ID';
        private static $tableClicksCampaignID = 'campaignID';
        private static $tableClicksBannerID = 'bannerID';
        private static $tableClicksUserID = 'userID';
        private static $tableClicksTimestamp = 'timestamp';
        private static $tableSponsoredCampaigns = 'sponsoredCampaign';
        private static $tableSponsoredCampaignsCampaignID = 'campaignID';
        private static $tableSponsoredCampaignsUserID = 'userID';
        private static $prefixedTables = false;
        
        /**
         * Class constructor 
         */
        public function __construct() {
            
        }
        
        /**
         * Return the WordPress Database Access Abstraction Object 
         * 
         * @global wpdb $wpdb
         * @return wpdb 
         */
        public static function getWpDB() {
            global $wpdb;
            
            if(!self::$prefixedTables):
                $prefix = $wpdb->prefix;
                // Append the Wordpress table prefix to the table names (if the prefix isn't already added)
                self::$tableClicks = (stripos(self::$tableClicks, $prefix) === 0 ? '' : $prefix) . self::$tableClicks;
                self::$tableSponsoredCampaigns = (stripos(self::$tableSponsoredCampaigns, $prefix) === 0 ? '' : $prefix) . self::$tableSponsoredCampaigns;
            endif;
            return $wpdb;
        }
        
        /**
         * Install the database tables
         */
        public static function install() {

            // Load the libraries
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');

            // Load the plugin version
            $plugin = get_plugin_data(ClickToDonate::FILE);
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

            // @TODO: remove all the metadata from the wordpress tables e.g., user table
        }
        
        
        /**
         * Register a visit in the system
         * 
         * @param int|object $post
         * @return boolean true if the visit was successfuly registered, false otherwise
         */
        public static function registerVisit($post) {
            $wpdb = self::getWpDB();

            $data = array(
                self::$tableClicksBannerID => ClickToDonateController::getPostID($post)/* ,
                self::$tableClicksTimestamp => current_time('mysql', true) */
            );
            $dataTypes = array(
                '%d'/* ,
                '%s' */
            );
            if ($userId = get_current_user_id()):
                $data[self::$tableClicksUserID] = $userId;
                $dataTypes[] = '%d';
            endif;

            if ($wpdb->insert(self::$tableClicks, $data, $dataTypes)):
                return true;
            endif;
            return false;
        }
        
        
        /**
         * Count the visits on a banner
         * 
         * @param int|object $post
         * @param int $user to filter the visits by a specific user
         * @return int with the number of visits
         */
        public static function countBannerVisits($post, $user = 0) {
            $wpdb = self::getWpDB();
            $post = ClickToDonateController::getPostID($post);
            $extra = '';
            $params = array($post);
            if (is_int($user) && absint($user)):
                $extra = ' AND `' . self::$tableClicksUserID . '`=%d';
                $params[] = $user;
            endif;
            if ($row = $wpdb->get_row($wpdb->prepare('
			    SELECT
				COUNT(*) AS `total`
			    FROM `' . self::$tableClicks . '` WHERE `' . self::$tableClicksBannerID . '`=%d ' . $extra . ';
			', $params), ARRAY_A)):
                return (int) $row['total'];
            endif;
            return 0;
        }
        
        
        /**
         * Get the timestamp of the last visit to the banner
         * @param int|object $post
         * @param int $user
         * @return int 
         */
        public static function getLastBannerVisit($post, $user = 0) {
            $wpdb = self::getWpDB();
            $post = ClickToDonateController::getPostID($post);
            $extra = '';
            $params = array($post);
            if (is_int($user) && absint($user)):
                $extra = ' AND `' . self::$tableClicksUserID . '`=%d';
                $params[] = $user;
            endif;
            if ($row = $wpdb->get_row($wpdb->prepare('
                    SELECT MAX(UNIX_TIMESTAMP(`' . self::$tableClicksTimestamp . '`)) AS `last`
                    FROM `' . self::$tableClicks . '` WHERE `' . self::$tableClicksBannerID . '`=%d ' . $extra . ';
                ', $params), ARRAY_A)):
                return (int) $row['last'];
            endif;
            return 0;
        }
    }
endif;