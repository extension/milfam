<?php

/**
 * Main class for Simple History
 */
class SimpleHistory {

	const NAME = "Simple History";
	const VERSION = "2.0.20";

	/**
	 * Capability required to view the history log
	 */
	private $view_history_capability;

	/**
	 * Capability required to view and edit the settings page
	 */
	private $view_settings_capability;

	/**
	 * Array with all instantiated loggers
	 */
	private $instantiatedLoggers;

	/**
	 * Array with all instantiated dropins
	 */
	private $instantiatedDropins;

	public $pluginBasename;

	/**
	 * Bool if gettext filter function should be active
	 * Should only be active during the load of a logger
	 */
	private $doFilterGettext = false;

	/**
	 * Used by gettext filter to temporarily store current logger
	 */
	private $doFilterGettext_currentLogger = null;

	/**
	 * Used to store latest translations used by __()
	 * Required to automagically determine orginal text and text domain
	 * for calls like this `SimpleLogger()->log( __("My translated message") );`
	 */
	public $gettextLatestTranslations = array();

	/**
	 * All registered settings tabs
	 */
	private $arr_settings_tabs = array();

	const DBTABLE = "simple_history";
	const DBTABLE_CONTEXTS = "simple_history_contexts";

	/** Slug for the settings menu */
	const SETTINGS_MENU_SLUG = "simple_history_settings_menu_slug";

	/** ID for the general settings section */
	const SETTINGS_SECTION_GENERAL_ID = "simple_history_settings_section_general";

	function __construct() {

		/**
		 * Fires before Simple History does it's init stuff
		 *
		 * @since 2.0
		 *
		 * @param SimpleHistory $SimpleHistory This class.
		 */
		do_action("simple_history/before_init", $this);

		$this->setupVariables();

		// Actions and filters, ordered by order specified in codex: http://codex.wordpress.org/Plugin_API/Action_Reference
		add_action('plugins_loaded', array($this, 'load_plugin_textdomain'));
		add_action('plugins_loaded', array($this, 'add_default_settings_tabs'));
		add_action('plugins_loaded', array($this, 'loadLoggers'));
		add_action('plugins_loaded', array($this, 'loadDropins'));

		// Run before loading of loggers and before menu items are added
		add_action('plugins_loaded', array($this, 'check_for_upgrade'), 5);

		add_action('plugins_loaded', array($this, 'setup_cron'));

		add_action('admin_menu', array($this, 'add_admin_pages'));
		add_action('admin_menu', array($this, 'add_settings'));

		add_action('admin_footer', array($this, "add_js_templates"));

		add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));

		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));

		add_action('admin_head', array($this, "onAdminHead"));
		add_action('admin_footer', array($this, "onAdminFooter"));

		// Filters and actions not called during regular boot
		add_filter("gettext", array($this, 'filter_gettext'), 20, 3);
		add_filter("gettext_with_context", array($this, 'filter_gettext_with_context'), 20, 4);

		add_filter('gettext', array( $this, "filter_gettext_storeLatestTranslations" ), 10, 3 );

		add_action('simple_history/history_page/before_gui', array($this, "output_quick_stats"));
		add_action('simple_history/dashboard/before_gui', array($this, "output_quick_stats"));

		add_action('wp_ajax_simple_history_api', array($this, 'api'));

		add_filter('plugin_action_links_simple-history/index.php', array($this, 'plugin_action_links'), 10, 4);

		/**
		 * Fires after Simple History has done it's init stuff
		 *
		 * @since 2.0
		 *
		 * @param SimpleHistory $SimpleHistory This class.
		 */
		do_action("simple_history/after_init", $this);
		
		// @todo run this when a debug const is defined and true
		/*
		add_filter("simple_history/log_argument/context", function($context, $level, $message, $logger) {

			$context["_debug_get"] = $this->json_encode( $_GET );
			$context["_debug_post"] = $this->json_encode( $_POST );
			$context["_debug_server"] = $this->json_encode( $_SERVER );
			$context["_debug_php_sapi_name"] = php_sapi_name();

			global $argv;
			$context["_debug_argv"] = $this->json_encode( $argv );

			// $context["_debug_env"] = $this->json_encode( $_ENV );
			
			$context["_debug_constants"] = $this->json_encode( get_defined_constants(true) );

			return $context;

		}, 10, 4);
		*/

	}

	function filter_gettext_storeLatestTranslations($translation, $text, $domain) {

		$array_max_size = 5;

		// Keep a listing of the n latest translation
		// when SimpleLogger->log() is called from anywhere we can then search for the
		// translated string among our n latest things and find it there, if it's translated
		//global $sh_latest_translations;
		$sh_latest_translations = $this->gettextLatestTranslations;

		$sh_latest_translations[$translation] = array(
			"translation" => $translation,
			"text" => $text,
			"domain" => $domain,
		);

		$arr_length = sizeof($sh_latest_translations);
		if ($arr_length > $array_max_size) {
			$sh_latest_translations = array_slice($sh_latest_translations, $arr_length - $array_max_size);
		}

		$this->gettextLatestTranslations = $sh_latest_translations;

		return $translation;

	}

	function setup_cron() {

		add_filter("simple_history/maybe_purge_db", array( $this, "maybe_purge_db") );

		if ( ! wp_next_scheduled('simple_history/maybe_purge_db') ) {
			wp_schedule_event(time(), 'daily', 'simple_history/maybe_purge_db');
			#error_log("not scheduled, so do schedule");
		} else {
			#error_log("is scheduled");
		}

		// Remove old schedule (only author dev sites should have it)
		$old_next_scheduled = wp_next_scheduled('simple_history/purge_db');
		if ( $old_next_scheduled ) {
			wp_unschedule_event($old_next_scheduled, 'simple_history/purge_db');
		}

	}

	public function testlog_old() {

		# Log that an email has been sent
		simple_history_add(array(
			"object_type" => "Email",
			"object_name" => "Hi there",
			"action" => "was sent",
		));

		# Will show “Plugin your_plugin_name Edited” in the history log
		simple_history_add("action=edited&object_type=plugin&object_name=your_plugin_name");

		# Will show the history item "Starship USS Enterprise repaired"
		simple_history_add("action=repaired&object_type=Starship&object_name=USS Enterprise");

		# Log with some extra details about the email
		simple_history_add(array(
			"object_type" => "Email",
			"object_name" => "Hi there",
			"action" => "was sent",
			"description" => "The database query to generate the email took .3 seconds. This is email number 4 that is sent to this user",
		));

	}

	public function onAdminHead() {

		if ($this->is_on_our_own_pages()) {

			do_action("simple_history/admin_head", $this);

		}

	}

	public function onAdminFooter() {

		if ($this->is_on_our_own_pages()) {

			do_action("simple_history/admin_footer", $this);

		}

	}

	/**
	 * Output JS templated into footer
	 */
	public function add_js_templates($hook) {

		if ($this->is_on_our_own_pages()) {

			?>

			<script type="text/html" id="tmpl-simple-history-base">

				<div class="SimpleHistory__waitingForFirstLoad">
					<img src="<?php echo admin_url("/images/spinner.gif");?>" alt="" width="20" height="20">
					<?php echo _x("Loading history...", "Message visible while waiting for log to load from server the first time", "simple-history")?>
				</div>

				<div class="SimpleHistoryLogitemsWrap">
					<div class="SimpleHistoryLogitems__beforeTopPagination"></div>
					<div class="SimpleHistoryLogitems__above"></div>
					<ul class="SimpleHistoryLogitems"></ul>
					<div class="SimpleHistoryLogitems__below"></div>
					<div class="SimpleHistoryLogitems__pagination"></div>
					<div class="SimpleHistoryLogitems__afterBottomPagination"></div>
				</div>

				<div class="SimpleHistoryLogitems__debug"></div>

			</script>

			<script type="text/html" id="tmpl-simple-history-logitems-pagination">

				<!-- this uses the (almost) the same html as WP does -->
				<div class="SimpleHistoryPaginationPages">
					<!--
					{{ data.page_rows_from }}–{{ data.page_rows_to }}
					<span class="SimpleHistoryPaginationDisplayNum"> of {{ data.total_row_count }} </span>
					-->
					<span class="SimpleHistoryPaginationLinks">
						<a
							data-direction="first"
							class="button SimpleHistoryPaginationLink SimpleHistoryPaginationLink--firstPage <# if ( data.api_args.paged <= 1 ) { #> disabled <# } #>"
							title="{{ data.strings.goToTheFirstPage }}"
							href="#">«</a>
						<a
							data-direction="prev"
							class="button SimpleHistoryPaginationLink SimpleHistoryPaginationLink--prevPage <# if ( data.api_args.paged <= 1 ) { #> disabled <# } #>"
							title="{{ data.strings.goToThePrevPage }}"
							href="#">‹</a>
						<span class="SimpleHistoryPaginationInput">
							<input class="SimpleHistoryPaginationCurrentPage" title="{{ data.strings.currentPage }}" type="text" name="paged" value="{{ data.api_args.paged }}" size="4">
							<?php _x("of", "page n of n", "simple-history")?>
							<span class="total-pages">{{ data.pages_count }}</span>
						</span>
						<a
							data-direction="next"
							class="button SimpleHistoryPaginationLink SimpleHistoryPaginationLink--nextPage <# if ( data.api_args.paged >= data.pages_count ) { #> disabled <# } #>"
							title="{{ data.strings.goToTheNextPage }}"
							href="#">›</a>
						<a
							data-direction="last"
							class="button SimpleHistoryPaginationLink SimpleHistoryPaginationLink--lastPage <# if ( data.api_args.paged >= data.pages_count ) { #> disabled <# } #>"
							title="{{ data.strings.goToTheLastPage }}"
							href="#">»</a>
					</span>
				</div>

			</script>

			<script type="text/html" id="tmpl-simple-history-logitems-modal">

				<div class="SimpleHistory-modal">
					<div class="SimpleHistory-modal__background"></div>
					<div class="SimpleHistory-modal__content">
						<div class="SimpleHistory-modal__contentInner">
							<img class="SimpleHistory-modal__contentSpinner" src="<?php echo admin_url("/images/spinner.gif");?>" alt="">
						</div>
						<div class="SimpleHistory-modal__contentClose">
							<button class="button">✕</button>
						</div>
					</div>
				</div>

			</script>

			<?php

			// Call plugins so they can add their js
			foreach ($this->instantiatedLoggers as $one_logger) {
				if (method_exists($one_logger["instance"], "adminJS")) {
					$one_logger["instance"]->adminJS();
				}
			}

		}

	}

	/**
	 * Base url is:
	 * /wp-admin/admin-ajax.php?action=simple_history_api
	 *
	 * Examples:
	 * http://playground-root.ep/wp-admin/admin-ajax.php?action=simple_history_api&posts_per_page=5&paged=1&format=html
	 *
	 */
	public function api() {

		global $wpdb;

		// Fake slow answers
		//sleep(2);
		//sleep(rand(0,3));
		$args = $_GET;
		unset($args["action"]);

		// Type = overview | ...
		$type = isset($_GET["type"]) ? $_GET["type"] : null;

		if (empty($args) || !$type) {

			wp_send_json_error(array(
				_x("Not enough args specified", "API: not enought arguments passed", "simple-history"),
			));

		}

		if (isset($args["id"])) {
			$args["post__in"] = array(
				$args["id"],
			);
		}

		$data = array();

		switch ($type) {

			case "overview":
			case "occasions":
			case "single":

				// API use SimpleHistoryLogQuery, so simply pass args on to that
				$logQuery = new SimpleHistoryLogQuery();
				$data = $logQuery->query($args);

				$data["api_args"] = $args;

				// Output can be array or HMTL
				if (isset($args["format"]) && "html" === $args["format"]) {

					$data["log_rows_raw"] = array();

					foreach ($data["log_rows"] as $key => $oneLogRow) {

						$args = array();
						if ($type == "single") {
							$args["type"] = "single";
						}

						$data["log_rows"][$key] = $this->getLogRowHTMLOutput($oneLogRow, $args);
						$data["num_queries"] = get_num_queries();

					}

				} else {

					$data["logRows"] = $logRows;
				}

				break;

			default:
				$data[] = "Nah.";

		}

		wp_send_json_success($data);

	}

	/**
	 * During the load of info for a logger we want to get a reference
	 * to the untranslated text too, because that's the version we want to store
	 * in the database.
	 */
	public function filter_gettext($translated_text, $untranslated_text, $domain) {

		if (isset($this->doFilterGettext) && $this->doFilterGettext) {

			$this->doFilterGettext_currentLogger->messages[] = array(
				"untranslated_text" => $untranslated_text,
				"translated_text" => $translated_text,
				"domain" => $domain,
				"context" => null,
			);

		}

		return $translated_text;

	}

	/**
	 * Store messages with context
	 */
	public function filter_gettext_with_context($translated_text, $untranslated_text, $context, $domain) {

		if (isset($this->doFilterGettext) && $this->doFilterGettext) {

			$this->doFilterGettext_currentLogger->messages[] = array(
				"untranslated_text" => $untranslated_text,
				"translated_text" => $translated_text,
				"domain" => $domain,
				"context" => $context,
			);

		}

		return $translated_text;

	}

	/**
	 * Load language files.
	 * Uses the method described here:
	 * http://geertdedeckere.be/article/loading-wordpress-language-files-the-right-way
	 *
	 * @since 2.0
	 */
	public function load_plugin_textdomain() {

		$domain = 'simple-history';

		// The "plugin_locale" filter is also used in load_plugin_textdomain()
		$locale = apply_filters('plugin_locale', get_locale(), $domain);

		load_textdomain($domain, WP_LANG_DIR . '/simple-history/' . $domain . '-' . $locale . '.mo');
		load_plugin_textdomain($domain, FALSE, dirname($this->plugin_basename) . '/languages/');

	}

	/**
	 * Setup variables and things
	 */
	public function setupVariables() {

		// Capability required to view history = for who will the History page be added
		$this->view_history_capability = "edit_pages";
		$this->view_history_capability = apply_filters("simple_history_view_history_capability", $this->view_history_capability);
		$this->view_history_capability = apply_filters("simple_history/view_history_capability", $this->view_history_capability);

		// Capability required to view settings
		$this->view_settings_capability = "manage_options";
		$this->view_settings_capability = apply_filters("simple_history_view_settings_capability", $this->view_settings_capability);
		$this->view_settings_capability = apply_filters("simple_history/view_settings_capability", $this->view_settings_capability);

		$this->plugin_basename = plugin_basename(__DIR__ . "/index.php");

	}

	/**
	 * Adds default tabs to settings
	 */
	public function add_default_settings_tabs() {

		// Add default settings tabs
		$this->arr_settings_tabs = array(

			array(
				"slug" => "settings",
				"name" => __("Settings", "simple-history"),
				"function" => array($this, "settings_output_general"),
			),

		);

		if (defined("SIMPLE_HISTORY_DEV") && SIMPLE_HISTORY_DEV) {

			$arr_dev_tabs = array(
				array(
					"slug" => "log",
					"name" => __("Log (debug)", "simple-history"),
					"function" => array($this, "settings_output_log"),
				),
				array(
					"slug" => "styles-example",
					"name" => __("Styles example (debug)", "simple-history"),
					"function" => array($this, "settings_output_styles_example"),
				),

			);

			$this->arr_settings_tabs = array_merge($this->arr_settings_tabs, $arr_dev_tabs);

		}

	}

	/**
	 * Load built in loggers from all files in /loggers
	 * and instantiates them
	 */
	public function loadLoggers() {

		$loggersDir = __DIR__ . "/loggers/";

		/**
		 * Filter the directory to load loggers from
		 *
		 * @since 2.0
		 *
		 * @param string $loggersDir Full directory path
		 */
		$loggersDir = apply_filters("simple_history/loggers_dir", $loggersDir);

		$loggersFiles = glob($loggersDir . "*.php");

		// SimpleLogger.php must be loaded first since the other loggers extend it
		require_once $loggersDir . "SimpleLogger.php";

		/**
		 * Filter the array with absolute paths to files as returned by glob function.
		 * Each file will be loaded and will be assumed to be a logger with a classname
		 * the same as the filename.
		 *
		 * @since 2.0
		 *
		 * @param array $loggersFiles Array with filenames
		 */
		$loggersFiles = apply_filters("simple_history/loggers_files", $loggersFiles);

		$arrLoggersToInstantiate = array();
		foreach ($loggersFiles as $oneLoggerFile) {

			if (basename($oneLoggerFile) == "SimpleLogger.php") {

				// SimpleLogger is already loaded

			} else {

				include_once $oneLoggerFile;

			}

			$arrLoggersToInstantiate[] = basename($oneLoggerFile, ".php");

		}

		/**
		 * Filter the array with names of loggers to instantiate.
		 *
		 * @since 2.0
		 *
		 * @param array $arrLoggersToInstantiate Array with class names
		 */
		$arrLoggersToInstantiate = apply_filters("simple_history/loggers_to_instantiate", $arrLoggersToInstantiate);
		// Instantiate each logger
		foreach ($arrLoggersToInstantiate as $oneLoggerName) {

			if (!class_exists($oneLoggerName)) {
				continue;
			}

			$loggerInstance = new $oneLoggerName($this);

			if (!is_subclass_of($loggerInstance, "SimpleLogger") && !is_a($loggerInstance, "SimpleLogger")) {
				continue;
			}

			$loggerInstance->loaded();

			// Tell gettext-filter to add untranslated messages
			$this->doFilterGettext = true;
			$this->doFilterGettext_currentLogger = $loggerInstance;

			$loggerInfo = $loggerInstance->getInfo();

			// Un-tell gettext filter
			$this->doFilterGettext = false;
			$this->doFilterGettext_currentLogger = null;

			// LoggerInfo contains all messages, both translated an not, by key.
			// Add messages to the loggerInstance
			$loopNum = 0;
			foreach ($loggerInfo["messages"] as $message_key => $message) {

				$loggerInstance->messages[$message_key] = $loggerInstance->messages[$loopNum];
				$loopNum++;

			}

			// Remove index keys, only keeping slug keys
			if (is_array($loggerInstance->messages)) {
				foreach ($loggerInstance->messages as $key => $val) {
					if (is_int($key)) {
						unset($loggerInstance->messages[$key]);
					}
				}
			}

			// Add logger to array of loggers
			$this->instantiatedLoggers[$loggerInstance->slug] = array(
				"name" => $loggerInfo["name"],
				"instance" => $loggerInstance,
			);

		}

		do_action("simple_history/loggers_loaded");

		#sf_d($this->instantiatedLoggers);exit;

	}

	/**
	 * Load built in dropins from all files in /dropins
	 * and instantiates them
	 */
	public function loadDropins() {

		$dropinsDir = __DIR__ . "/dropins/";

		/**
		 * Filter the directory to load loggers from
		 *
		 * @since 2.0
		 *
		 * @param string $dropinsDir Full directory path
		 */
		$dropinsDir = apply_filters("simple_history/dropins_dir", $dropinsDir);

		$dropinsFiles = glob($dropinsDir . "*.php");

		/**
		 * Filter the array with absolute paths to files as returned by glob function.
		 * Each file will be loaded and will be assumed to be a dropin with a classname
		 * the same as the filename.
		 *
		 * @since 2.0
		 *
		 * @param array $dropinsFiles Array with filenames
		 */
		$dropinsFiles = apply_filters("simple_history/dropins_files", $dropinsFiles);

		$arrDropinsToInstantiate = array();

		foreach ($dropinsFiles as $oneDropinFile) {

			// path/path/simplehistory/dropins/SimpleHistoryDonateDropin.php => SimpleHistoryDonateDropin
			$oneDropinFileBasename = basename($oneDropinFile, ".php");

			/**
			 * Filter to completely skip loading of dropin
			 * complete filer name will be like:
			 * simple_history/dropin/load_dropin_SimpleHistoryRSSDropin
			 *
			 * @since 2.0.6
			 *
			 * @param bool if to load the dropin. return false to not load it.
			 */
			$load_dropin = apply_filters("simple_history/dropin/load_dropin_{$oneDropinFileBasename}", true);

			if (!$load_dropin) {
				continue;
			}

			include_once $oneDropinFile;

			$arrDropinsToInstantiate[] = $oneDropinFileBasename;

		}

		/**
		 * Filter the array with names of dropin to instantiate.
		 *
		 * @since 2.0
		 *
		 * @param array $arrDropinsToInstantiate Array with class names
		 */
		$arrDropinsToInstantiate = apply_filters("simple_history/dropins_to_instantiate", $arrDropinsToInstantiate);

		// Instantiate each dropin
		foreach ($arrDropinsToInstantiate as $oneDropinName) {

			if (!class_exists($oneDropinName)) {
				continue;
			}

			$this->instantiatedDropins[$oneDropinName] = array(
				"name" => $oneDropinName,
				"instance" => new $oneDropinName($this),
			);
		}

	}

	/**
	 * Gets the pager size,
	 * i.e. the number of items to show on each page in the history
	 *
	 * @return int
	 */
	function get_pager_size() {

		$pager_size = get_option("simple_history_pager_size", 5);

		/**
		 * Filter the pager size setting
		 *
		 * @since 2.0
		 *
		 * @param int $pager_size
		 */
		$pager_size = apply_filters("simple_history/pager_size", $pager_size);

		return $pager_size;

	}

	/**
	 * Show a link to our settings page on the Plugins -> Installed Plugins screen
	 */
	function plugin_action_links($actions, $b, $c, $d) {

		// Only add link if user has the right to view the settings page
		if (!current_user_can($this->view_settings_capability)) {
			return $actions;
		}

		$settings_page_url = menu_page_url(SimpleHistory::SETTINGS_MENU_SLUG, 0);

		$actions[] = "<a href='$settings_page_url'>" . __("Settings", "simple-history") . "</a>";

		return $actions;

	}

	/**
	 * Maybe add a dashboard widget,
	 * requires current user to have view history capability
	 * and a setting to show dashboard to be set
	 */
	function add_dashboard_widget() {

		if ($this->setting_show_on_dashboard() && current_user_can($this->view_history_capability)) {

			wp_add_dashboard_widget("simple_history_dashboard_widget", __("Simple History", 'simple-history'), array($this, "dashboard_widget_output"));

		}
	}

	/**
	 * Output html for the dashboard widget
	 */
	function dashboard_widget_output() {

		$pager_size = $this->get_pager_size();

		/**
		 * Filter the pager size setting for the dashboard
		 *
		 * @since 2.0
		 *
		 * @param int $pager_size
		 */
		$pager_size = apply_filters("simple_history/dashboard_pager_size", $pager_size);

		do_action("simple_history/dashboard/before_gui", $this);

		?>
		<div class="SimpleHistoryGui"
			 data-pager-size='<?php echo $pager_size?>'
			 ></div>
		<?php

	}

	function is_on_our_own_pages($hook = "") {

		$current_screen = get_current_screen();

		if ($current_screen && $current_screen->base == "settings_page_" . SimpleHistory::SETTINGS_MENU_SLUG) {

			return true;

		} else if ($current_screen && $current_screen->base == "dashboard_page_simple_history_page") {

			return true;

		} else if (($hook == "settings_page_" . SimpleHistory::SETTINGS_MENU_SLUG) || ($this->setting_show_on_dashboard() && $hook == "index.php") || ($this->setting_show_as_page() && $hook == "dashboard_page_simple_history_page")) {

			return true;

		} else if ($current_screen && $current_screen->base == "dashboard" && $this->setting_show_on_dashboard()) {

			return true;

		}

		return false;
	}

	/**
	 * Enqueue styles and scripts for Simple History but only to our own pages.
	 *
	 * Only adds scripts to pages where the log is shown or the settings page.
	 */
	function enqueue_admin_scripts($hook) {

		if ($this->is_on_our_own_pages()) {

			add_thickbox();

			$plugin_url = plugin_dir_url(__FILE__);
			wp_enqueue_style("simple_history_styles", $plugin_url . "css/styles.css", false, SimpleHistory::VERSION);
			wp_enqueue_script("simple_history_script", $plugin_url . "js/scripts.js", array("jquery", "backbone", "wp-util"), SimpleHistory::VERSION, true);

			wp_enqueue_script("select2", $plugin_url . "/js/select2/select2.min.js", array("jquery"));
			wp_enqueue_style("select2", $plugin_url . "/js/select2/select2.css");

			// Translations that we use in JavaScript
			wp_localize_script('simple_history_script', 'simple_history_script_vars', array(
				'settingsConfirmClearLog' => __("Remove all log items?", 'simple-history'),
				'pagination' => array(
					'goToTheFirstPage' => __("Go to the first page", 'simple-history'),
					'goToThePrevPage' => __("Go to the previous page", 'simple-history'),
					'goToTheNextPage' => __("Go to the next page", 'simple-history'),
					'goToTheLastPage' => __("Go to the last page", 'simple-history'),
					'currentPage' => __("Current page", 'simple-history'),
				),
				"loadLogAPIError" => __("Oups, the log could not be loaded right now.", 'simple-history'),
				"logNoHits" => __("Your search did not match any history events.", "simple-history"),
			));

			// Call plugins adminCSS-method, so they can add their CSS
			foreach ($this->instantiatedLoggers as $one_logger) {
				if (method_exists($one_logger["instance"], "adminCSS")) {
					$one_logger["instance"]->adminCSS();
				}
			}

			/**
			 * Fires when the admin scripts have been enqueued.
			 * Only fires on any of the pages where Simple History is used
			 *
			 * @since 2.0
			 *
			 * @param SimpleHistory $SimpleHistory This class.
			 */
			do_action("simple_history/enqueue_admin_scripts", $this);

		}

	}

	function filter_option_page_capability($capability) {
		return $capability;
	}

	/**
	 * Check if plugin version have changed, i.e. has been upgraded
	 * If upgrade is detected then maybe modify database and so on for that version
	 */
	function check_for_upgrade() {

		global $wpdb;

		$db_version = get_option("simple_history_db_version");
		$table_name = $wpdb->prefix . SimpleHistory::DBTABLE;
		$table_name_contexts = $wpdb->prefix . SimpleHistory::DBTABLE_CONTEXTS;
		$first_install = false;

		// If no db_version is set then this
		// is a version of Simple History < 0.4
		// or it's a first install
		// Fix database not using UTF-8
		if (false === $db_version) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			// Table creation, used to be in register_activation_hook
			/*
			$sql = "CREATE TABLE " . $table_name . " (
			id int(10) NOT NULL AUTO_INCREMENT,
			date datetime NOT NULL,
			action varchar(255) NOT NULL COLLATE utf8_general_ci,
			object_type varchar(255) NOT NULL COLLATE utf8_general_ci,
			object_subtype VARCHAR(255) NOT NULL COLLATE utf8_general_ci,
			user_id int(10) NOT NULL,
			object_id int(10) NOT NULL,
			object_name varchar(255) NOT NULL COLLATE utf8_general_ci,
			action_description longtext,
			PRIMARY KEY  (id)
			) CHARACTER SET=utf8;";
			dbDelta($sql);
			 */

			// We change the varchar size to add one num just to force update of encoding. dbdelta didn't see it otherwise.
			// This table is missing action_description, but we add that later on
			$sql = "CREATE TABLE " . $table_name . " (
			  id bigint(20) NOT NULL AUTO_INCREMENT,
			  date datetime NOT NULL,
			  action VARCHAR(256) NOT NULL COLLATE utf8_general_ci,
			  object_type VARCHAR(256) NOT NULL COLLATE utf8_general_ci,
			  object_subtype VARCHAR(256) NOT NULL COLLATE utf8_general_ci,
			  user_id int(10) NOT NULL,
			  object_id int(10) NOT NULL,
			  object_name VARCHAR(256) NOT NULL COLLATE utf8_general_ci,
			  PRIMARY KEY  (id)
			) CHARACTER SET=utf8;";

			// Upgrade db / fix utf for varchars
			dbDelta($sql);

			// Fix UTF-8 for table
			$sql = sprintf('alter table %1$s charset=utf8;', $table_name);
			$wpdb->query($sql);

			$db_version_prev = $db_version;
			$db_version = 1;

			update_option("simple_history_db_version", $db_version);

			// We are not 100% sure that this is a first install,
			// but it is at least a very old version that is being updated
			$first_install = true;

		}// done pre db ver 1 things

		// If db version is 1 then upgrade to 2
		// Version 2 added the action_description column
		if (1 == intval($db_version)) {

			// Add column for action description in non-translatable free text
			$sql = "ALTER TABLE {$table_name} ADD COLUMN action_description longtext";
			$wpdb->query($sql);

			$db_version_prev = $db_version;
			$db_version = 2;

			update_option("simple_history_db_version", $db_version);

		}

		// Check that all options we use are set to their defaults, if they miss value
		// Each option that is missing a value will make a sql call otherwise = unnecessary
		$arr_options = array(
			array(
				"name" => "simple_history_show_as_page",
				"default_value" => 1,
			),
			array(
				"name" => "simple_history_show_on_dashboard",
				"default_value" => 1,
			),
		);

		foreach ($arr_options as $one_option) {

			if (false === ($option_value = get_option($one_option["name"]))) {

				// Value is not set in db, so set it to a default
				update_option($one_option["name"], $one_option["default_value"]);

			}
		}

		/**
		 * If db_version is 2 then upgrade to 3:
		 * - Add some fields to existing table wp_simple_history_contexts
		 * - Add all new table wp_simple_history_contexts
		 *
		 * @since 2.0
		 */
		if (2 == intval($db_version)) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			// Update old table
			$sql = "
				CREATE TABLE {$table_name} (
				  id bigint(20) NOT NULL AUTO_INCREMENT,
				  date datetime NOT NULL,
				  logger varchar(30) DEFAULT NULL,
				  level varchar(20) DEFAULT NULL,
				  message varchar(255) DEFAULT NULL,
				  occasionsID varchar(32) DEFAULT NULL,
				  type varchar(16) DEFAULT NULL,
				  initiator varchar(16) DEFAULT NULL,
				  action varchar(255) NOT NULL,
				  object_type varchar(255) NOT NULL,
				  object_subtype varchar(255) NOT NULL,
				  user_id int(10) NOT NULL,
				  object_id int(10) NOT NULL,
				  object_name varchar(255) NOT NULL,
				  action_description longtext,
				  PRIMARY KEY  (id),
				  KEY date (date),
				  KEY loggerdate (logger, date)
				) CHARSET=utf8;";

			dbDelta($sql);

			// Add context table
			$sql = "
				CREATE TABLE {$table_name_contexts} (
				  context_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				  history_id bigint(20) unsigned NOT NULL,
				  `key` varchar(255) DEFAULT NULL,
				  value longtext,
				  PRIMARY KEY  (context_id),
				  KEY history_id (history_id),
				  KEY `key` (`key`)
				) CHARSET=utf8;
			";

			$wpdb->query($sql);

			$db_version_prev = $db_version;
			$db_version = 3;
			update_option("simple_history_db_version", $db_version);

			// Update old items to use SimpleLegacyLogger
			$sql = sprintf('
					UPDATE %1$s
					SET
						logger = "SimpleLegacyLogger",
						level = "info"
					WHERE logger IS NULL
				',
				$table_name
			);

			$wpdb->query($sql);

			// Say welcome, however loggers are not added this early so we need to
			// use a filter to load it later
			add_action("simple_history/loggers_loaded", array($this, "addWelcomeLogMessage"));

		}// db version 2 » 3

		/**
		 * If db version = 3
		 * then we need to update database to allow null values for some old columns
		 * that used to work in pre wp 4.1 beta, but since 4.1 wp uses STRICT_ALL_TABLES
		 * WordPress Commit: https://github.com/WordPress/WordPress/commit/f17d168a0f72211a9bfd9d3fa680713069871bb6
		 *
		 * @since 2.0
		 */
		if (3 == intval($db_version)) {

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$sql = sprintf('
					ALTER TABLE %1$s
					MODIFY `action` varchar(255) NULL,
					MODIFY `object_type` varchar(255) NULL,
					MODIFY `object_subtype` varchar(255) NULL,
					MODIFY `user_id` int(10) NULL,
					MODIFY `object_id` int(10) NULL,
					MODIFY `object_name` varchar(255) NULL
				',
				$table_name
			);
			$wpdb->query($sql);

			$db_version_prev = $db_version;
			$db_version = 4;

			update_option("simple_history_db_version", $db_version);

		}// end db version 3 » 4

	}// end check_for_upgrade

	/**
	 * Greet users to version 2!
	 */
	public function addWelcomeLogMessage() {

		SimpleLogger()->info(
			"Welcome to Simple History 2! Hope you will enjoy this plugin.
			Found bugs? Got great ideas? Send them to the plugin developer at par.thernstrom@gmail.com.",
			array(
				"_initiator" => SimpleLoggerLogInitiators::WORDPRESS,
			)
		);

	}

	public function registerSettingsTab($arr_tab_settings) {

		$this->arr_settings_tabs[] = $arr_tab_settings;

	}

	public function getSettingsTabs() {

		return $this->arr_settings_tabs;

	}

	/**
	 * Output HTML for the settings page
	 * Called from add_options_page
	 */
	function settings_page_output() {

		$arr_settings_tabs = $this->getSettingsTabs();

		?>
		<div class="wrap">

			<h2 class="SimpleHistoryPageHeadline">
				<div class="dashicons dashicons-backup SimpleHistoryPageHeadline__icon"></div>
				<?php _e("Simple History Settings", "simple-history")?>
			</h2>

			<?php
$active_tab = isset($_GET["selected-tab"]) ? $_GET["selected-tab"] : "settings";
		$settings_base_url = menu_page_url(SimpleHistory::SETTINGS_MENU_SLUG, 0);
		?>

			<h3 class="nav-tab-wrapper">
				<?php
foreach ($arr_settings_tabs as $one_tab) {

			$tab_slug = $one_tab["slug"];

			printf(
				'<a href="%3$s" class="nav-tab %4$s">%1$s</a>',
				$one_tab["name"], // 1
				$tab_slug, // 2
				add_query_arg("selected-tab", $tab_slug, $settings_base_url), // 3
				$active_tab == $tab_slug ? "nav-tab-active" : ""// 4
			);

		}
		?>
			</h3>

			<?php

		// Output contents for selected tab
		$arr_active_tab = wp_filter_object_list($arr_settings_tabs, array("slug" => $active_tab));
		$arr_active_tab = current($arr_active_tab);

		// We must have found an active tab and it must have a callable function
		if (!$arr_active_tab || !is_callable($arr_active_tab["function"])) {
			wp_die(__("No valid callback found", "simple-history"));
		}

		$args = array(
			"arr_active_tab" => $arr_active_tab,
		);

		call_user_func_array($arr_active_tab["function"], $args);

		?>

		</div>
		<?php

	}

	public function settings_output_log() {

		include __DIR__ . "/templates/settings-log.php";

	}

	public function settings_output_general() {

		include __DIR__ . "/templates/settings-general.php";

	}

	public function settings_output_styles_example() {

		include __DIR__ . "/templates/settings-style-example.php";

	}

	/**
	 * Content for section intro. Leave it be, even if empty.
	 * Called from add_sections_setting.
	 */
	function settings_section_output() {

	}

	/**
	 * Add pages (history page and settings page)
	 */
	function add_admin_pages() {

		// Add a history page as a sub-page below the Dashboard menu item
		if ($this->setting_show_as_page()) {

			add_dashboard_page(
				SimpleHistory::NAME,
				_x("Simple History", 'dashboard menu name', 'simple-history'),
				$this->view_history_capability,
				"simple_history_page",
				array($this, "history_page_output")
			);

		}

		// Add a settings page
		$show_settings_page = true;
		$show_settings_page = apply_filters("simple_history_show_settings_page", $show_settings_page);
		$show_settings_page = apply_filters("simple_history/show_settings_page", $show_settings_page);
		if ($show_settings_page) {

			add_options_page(
				__('Simple History Settings', "simple-history"),
				SimpleHistory::NAME,
				$this->view_settings_capability,
				SimpleHistory::SETTINGS_MENU_SLUG,
				array($this, 'settings_page_output')
			);

		}

	}

	/*
	 * Add setting sections and settings for the settings page
	 * Also maybe save some settings before outputing them
	 */
	function add_settings() {

		// Clear the log if clear button was clicked in settings
		if (isset($_GET["simple_history_clear_log_nonce"]) && wp_verify_nonce($_GET["simple_history_clear_log_nonce"], 'simple_history_clear_log')) {

			$this->clear_log();
			$msg = __("Cleared database", 'simple-history');
			add_settings_error("simple_history_rss_feed_regenerate_secret", "simple_history_rss_feed_regenerate_secret", $msg, "updated");
			set_transient('settings_errors', get_settings_errors(), 30);

			$goback = add_query_arg('settings-updated', 'true', wp_get_referer());
			wp_redirect($goback);
			exit;

		}

		// Section for general options
		// Will contain settings like where to show simple history and number of items
		$settings_section_general_id = self::SETTINGS_SECTION_GENERAL_ID;
		add_settings_section(
			$settings_section_general_id,
			"", // No title __("General", "simple-history"),
			array($this, "settings_section_output"),
			SimpleHistory::SETTINGS_MENU_SLUG// same slug as for options menu page
		);

		// Settings for the general settings section
		// Each setting = one row in the settings section
		// add_settings_field( $id, $title, $callback, $page, $section, $args );

		// Checkboxes for where to show simple history
		add_settings_field(
			"simple_history_show_where",
			__("Show history", "simple-history"),
			array($this, "settings_field_where_to_show"),
			SimpleHistory::SETTINGS_MENU_SLUG,
			$settings_section_general_id
		);

		// Nonces for show where inputs
		register_setting("simple_history_settings_group", "simple_history_show_on_dashboard");
		register_setting("simple_history_settings_group", "simple_history_show_as_page");

		// Dropdown number if items to show
		add_settings_field(
			"simple_history_number_of_items",
			__("Number of items per page", "simple-history"),
			array($this, "settings_field_number_of_items"),
			SimpleHistory::SETTINGS_MENU_SLUG,
			$settings_section_general_id
		);

		// Nonces for number of items inputs
		register_setting("simple_history_settings_group", "simple_history_pager_size");

		// Link to clear log
		add_settings_field(
			"simple_history_clear_log",
			__("Clear log", "simple-history"),
			array($this, "settings_field_clear_log"),
			SimpleHistory::SETTINGS_MENU_SLUG,
			$settings_section_general_id
		);

	}

	/**
	 * Output for page with the history
	 */
	function history_page_output() {

		//global $simple_history;

		//$this->purge_db();

		global $wpdb;

		$pager_size = $this->get_pager_size();

		/**
		 * Filter the pager size setting for the history page
		 *
		 * @since 2.0
		 *
		 * @param int $pager_size
		 */
		$pager_size = apply_filters("simple_history/page_pager_size", $pager_size);

		?>

		<div class="wrap SimpleHistoryWrap">

			<h2 class="SimpleHistoryPageHeadline">
				<div class="dashicons dashicons-backup SimpleHistoryPageHeadline__icon"></div>
				<?php echo _x("Simple History", 'history page headline', 'simple-history')?>
			</h2>

			<?php
/**
		 * Fires before the gui div
		 *
		 * @since 2.0
		 *
		 * @param SimpleHistory $SimpleHistory This class.
		 */
		do_action("simple_history/history_page/before_gui", $this);
		?>

			<div class="SimpleHistoryGuiWrap">

				<div class="SimpleHistoryGui"
					 data-pager-size='<?php echo $pager_size?>'
					 ></div>

				<?php

		/**
		 * Fires after the gui div
		 *
		 * @since 2.0
		 *
		 * @param SimpleHistory $SimpleHistory This class.
		 */
		do_action("simple_history/history_page/after_gui", $this);

		?>

			</div>

		</div>

		<?php

	}

	/**
	 * Get setting if plugin should be visible on dasboard.
	 * Defaults to true
	 *
	 * @return bool
	 */
	function setting_show_on_dashboard() {

		$show_on_dashboard = get_option("simple_history_show_on_dashboard", 1);
		$show_on_dashboard = apply_filters("simple_history_show_on_dashboard", $show_on_dashboard);
		return (bool) $show_on_dashboard;

	}

	/**
	 * Should simple history be shown as a page
	 * Defaults to true
	 *
	 * @return bool
	 */
	function setting_show_as_page() {

		$setting = get_option("simple_history_show_as_page", 1);
		$setting = apply_filters("simple_history_show_as_page", $setting);
		return (bool) $setting;

	}

	/**
	 * Settings field for how many rows/items to show in log
	 */
	function settings_field_number_of_items() {

		$current_pager_size = $this->get_pager_size();

		?>
		<select name="simple_history_pager_size">
			<option <?php echo $current_pager_size == 5 ? "selected" : ""?> value="5">5</option>
			<option <?php echo $current_pager_size == 10 ? "selected" : ""?> value="10">10</option>
			<option <?php echo $current_pager_size == 15 ? "selected" : ""?> value="15">15</option>
			<option <?php echo $current_pager_size == 20 ? "selected" : ""?> value="20">20</option>
			<option <?php echo $current_pager_size == 25 ? "selected" : ""?> value="25">25</option>
			<option <?php echo $current_pager_size == 30 ? "selected" : ""?> value="30">30</option>
			<option <?php echo $current_pager_size == 40 ? "selected" : ""?> value="40">40</option>
			<option <?php echo $current_pager_size == 50 ? "selected" : ""?> value="50">50</option>
			<option <?php echo $current_pager_size == 75 ? "selected" : ""?> value="75">75</option>
			<option <?php echo $current_pager_size == 100 ? "selected" : ""?> value="100">100</option>
		</select>
		<?php

	}

	/**
	 * Settings field for where to show the log, page or dashboard
	 */
	function settings_field_where_to_show() {

		$show_on_dashboard = $this->setting_show_on_dashboard();
		$show_as_page = $this->setting_show_as_page();
		?>

		<input <?php echo $show_on_dashboard ? "checked='checked'" : ""?> type="checkbox" value="1" name="simple_history_show_on_dashboard" id="simple_history_show_on_dashboard" class="simple_history_show_on_dashboard" />
		<label for="simple_history_show_on_dashboard"><?php _e("on the dashboard", 'simple-history')?></label>

		<br />

		<input <?php echo $show_as_page ? "checked='checked'" : ""?> type="checkbox" value="1" name="simple_history_show_as_page" id="simple_history_show_as_page" class="simple_history_show_as_page" />
		<label for="simple_history_show_as_page"><?php _e("as a page under the dashboard menu", 'simple-history')?></label>

		<?php
}

	/**
	 * Settings section to clear database
	 */
	function settings_field_clear_log() {

		$clear_link = add_query_arg("", "");
		$clear_link = wp_nonce_url($clear_link, "simple_history_clear_log", "simple_history_clear_log_nonce");
		$clear_days = $this->get_clear_history_interval();

		echo "<p>";
		if ($clear_days > 0) {
			echo sprintf(__('Items in the database are automatically removed after %1$s days.', "simple-history"), $clear_days);
		} else {
			_e('Items in the database are kept forever.', 'simple-history');
		}
		echo "</p>";

		printf('<p><a class="button js-SimpleHistory-Settings-ClearLog" href="%2$s">%1$s</a></p>', __('Clear log now', 'simple-history'), $clear_link);
	}

	/**
	 * How old log entried are allowed to be.
	 * 0 = don't delete old entries.
	 *
	 * @return int Number of days.
	 */
	function get_clear_history_interval() {

		$days = 60;

		$days = (int) apply_filters("simple_history_db_purge_days_interval", $days);
		$days = (int) apply_filters("simple_history/db_purge_days_interval", $days);

		return $days;

	}

	/**
	 * Removes all items from the log
	 */
	function clear_log() {

		global $wpdb;

		$tableprefix = $wpdb->prefix;

		$simple_history_table = SimpleHistory::DBTABLE;
		$simple_history_context_table = SimpleHistory::DBTABLE_CONTEXTS;

		// Get number of rows before delete
		$sql_num_rows = "SELECT count(id) AS num_rows FROM {$tableprefix}{$simple_history_table}";
		$num_rows = $wpdb->get_var($sql_num_rows, 0);

		$sql = "DELETE FROM {$tableprefix}{$simple_history_table}";
		$wpdb->query($sql);

		$sql = "DELETE FROM {$tableprefix}{$simple_history_context_table}";
		$wpdb->query($sql);

		// Zero state sucks
		SimpleLogger()->info(
			__("The log for Simple History was cleared ({num_rows} rows were removed).", "simple-history"),
			array(
				"num_rows" => $num_rows,
			)
		);

		$this->get_cache_incrementor(true);

	}

	/**
	 * Runs the purge_db() method sometimes
	 * We don't want to call it each time because it performs SQL queries
	 *
	 * @since 2.0.17
	 */
	function maybe_purge_db() {

		/*if ( ! is_admin() ) {
			return;
		}*/

		// How often should we try to do this?
		// Once a day = a bit tiresome
		// Let's go with sundays; purge the log on sundays

		// day of week, 1 = mon, 7 = sun
		$day_of_week = date('N');
		if ( 7 === (int) $day_of_week ) {

			$this->purge_db();

		}

	}

	/**
	 * Removes old entries from the db
	 */
	function purge_db() {

		// SimpleLogger()->debug("Simple History is running purge_db()");

		$do_purge_history = true;

		$do_purge_history = apply_filters("simple_history_allow_db_purge", $do_purge_history);
		$do_purge_history = apply_filters("simple_history/allow_db_purge", $do_purge_history);

		if (!$do_purge_history) {
			return;
		}

		$days = $this->get_clear_history_interval();

		// Never clear log if days = 0
		if (0 == $days) {
			return;
		}

		global $wpdb;

		$table_name = $wpdb->prefix . SimpleHistory::DBTABLE;
		$table_name_contexts = $wpdb->prefix . SimpleHistory::DBTABLE_CONTEXTS;

		// Get id of rows to delete
		$sql = "SELECT id FROM {$table_name} WHERE DATE_ADD(date, INTERVAL $days DAY) < now()";

		$ids_to_delete = $wpdb->get_col($sql);

		if (empty($ids_to_delete)) {
			// Nothing to delete
			return;
		}

		$sql_ids_in = implode(",", $ids_to_delete);

		// Add number of deleted rows to total_rows option
		$prev_total_rows = (int) get_option("simple_history_total_rows", 0);
		$total_rows = $prev_total_rows + sizeof($ids_to_delete);
		update_option("simple_history_total_rows", $total_rows);

		// Remove rows + contexts
		$sql_delete_history = "DELETE FROM {$table_name} WHERE id IN ($sql_ids_in)";
		$sql_delete_history_context = "DELETE FROM {$table_name_contexts} WHERE history_id IN ($sql_ids_in)";

		$wpdb->query($sql_delete_history);
		$wpdb->query($sql_delete_history_context);

		$message = _nx(
			"Simple History removed one event that were older than {days} days",
			"Simple History removed {num_rows} events that were older than {days} days",
			"Database is being cleared automagically",
			"simple-history"
		);

		SimpleLogger()->info(
			$message,
			array(
				"days" => $days,
				"num_rows" => sizeof($ids_to_delete),
			)
		);

		$this->get_cache_incrementor(true);

	}

	/**
	 * Return plain text output for a log row
	 * Uses the getLogRowPlainTextOutput of the logger that logged the row
	 * with fallback to SimpleLogger if logger is not available
	 *
	 * @param array $row
	 * @return string
	 */
	public function getLogRowPlainTextOutput($row) {

		$row_logger = $row->logger;
		$logger = null;
		$row->context = isset($row->context) && is_array($row->context) ? $row->context : array();

		if (!isset($row->context["_message_key"])) {
			$row->context["_message_key"] = null;
		}

		// Fallback to SimpleLogger if no logger exists for row
		if (!isset($this->instantiatedLoggers[$row_logger])) {
			$row_logger = "SimpleLogger";
		}

		$logger = $this->instantiatedLoggers[$row_logger]["instance"];

		return $logger->getLogRowPlainTextOutput($row);

	}

	/**
	 * Return header output for a log row
	 * Uses the getLogRowHeaderOutput of the logger that logged the row
	 * with fallback to SimpleLogger if logger is not available
	 *
	 * Loggers are discouraged to override this in the loggers,
	 * because the output should be the same for all items in the gui
	 *
	 * @param array $row
	 * @return string
	 */
	public function getLogRowHeaderOutput($row) {

		$row_logger = $row->logger;
		$logger = null;
		$row->context = isset($row->context) && is_array($row->context) ? $row->context : array();

		// Fallback to SimpleLogger if no logger exists for row
		if (!isset($this->instantiatedLoggers[$row_logger])) {
			$row_logger = "SimpleLogger";
		}

		$logger = $this->instantiatedLoggers[$row_logger]["instance"];

		return $logger->getLogRowHeaderOutput($row);

	}

	/**
	 *
	 *
	 * @param array $row
	 * @return string
	 */
	private function getLogRowSenderImageOutput($row) {

		$row_logger = $row->logger;
		$logger = null;
		$row->context = isset($row->context) && is_array($row->context) ? $row->context : array();

		// Fallback to SimpleLogger if no logger exists for row
		if (!isset($this->instantiatedLoggers[$row_logger])) {
			$row_logger = "SimpleLogger";
		}

		$logger = $this->instantiatedLoggers[$row_logger]["instance"];

		return $logger->getLogRowSenderImageOutput($row);

	}

	public function getLogRowDetailsOutput($row) {

		$row_logger = $row->logger;
		$logger = null;
		$row->context = isset($row->context) && is_array($row->context) ? $row->context : array();

		// Fallback to SimpleLogger if no logger exists for row
		if (!isset($this->instantiatedLoggers[$row_logger])) {
			$row_logger = "SimpleLogger";
		}

		$logger = $this->instantiatedLoggers[$row_logger]["instance"];

		return $logger->getLogRowDetailsOutput($row);

	}

	/**
	 * Works like json_encode, but adds JSON_PRETTY_PRINT if the current php version supports it
	 * i.e. PHP is 5.4.0 or greated
	 *
	 * @param $value array|object|string|whatever that is json_encode'able
	 */
	public static function json_encode($value) {

		return version_compare(PHP_VERSION, '5.4.0') >= 0 ? json_encode($value, JSON_PRETTY_PRINT) : json_encode($value);

	}

	/**
	 * Returns true if $haystack ends with $needle
	 * @param string $haystack
	 * @param string $needle
	 */
	public static function ends_with($haystack, $needle) {
		return $needle === substr($haystack, -strlen($needle));
	}

	/**
	 * Returns the HTML output for a log row, to be used in the GUI/Activity Feed
	 *
	 * @param array $oneLogRow SimpleHistoryLogQuery array with data from SimpleHistoryLogQuery
	 * @return string
	 */
	public function getLogRowHTMLOutput($oneLogRow, $args) {

		$defaults = array(
			"type" => "overview", // or "single" to include more stuff
		);

		$args = wp_parse_args($args, $defaults);

		$header_html = $this->getLogRowHeaderOutput($oneLogRow);
		$plain_text_html = $this->getLogRowPlainTextOutput($oneLogRow);
		$sender_image_html = $this->getLogRowSenderImageOutput($oneLogRow);

		// Details = for example thumbnail of media
		$details_html = trim($this->getLogRowDetailsOutput($oneLogRow));
		if ($details_html) {

			$details_html = sprintf(
				'<div class="SimpleHistoryLogitem__details">%1$s</div>',
				$details_html
			);

		}

		// subsequentOccasions = including the current one
		$occasions_count = $oneLogRow->subsequentOccasions - 1;
		$occasions_html = "";

		if ($occasions_count > 0) {

			$occasions_html = '<div class="SimpleHistoryLogitem__occasions">';

			$occasions_html .= '<a href="#" class="SimpleHistoryLogitem__occasionsLink">';
			$occasions_html .= sprintf(
				_n('+%1$s similar event', '+%1$s similar events', $occasions_count, "simple-history"),
				$occasions_count
			);
			$occasions_html .= '</a>';

			$occasions_html .= '<span class="SimpleHistoryLogitem__occasionsLoading">';
			$occasions_html .= sprintf(
				__('Loading…', "simple-history"),
				$occasions_count
			);
			$occasions_html .= '</span>';

			$occasions_html .= '<span class="SimpleHistoryLogitem__occasionsLoaded">';
			$occasions_html .= sprintf(
				__('Showing %1$s more', "simple-history"),
				$occasions_count
			);
			$occasions_html .= '</span>';

			$occasions_html .= '</div>';

		}

		$data_attrs = "";
		$data_attrs .= sprintf(' data-row-id="%1$d" ', $oneLogRow->id);
		$data_attrs .= sprintf(' data-occasions-count="%1$d" ', $occasions_count);
		$data_attrs .= sprintf(' data-occasions-id="%1$s" ', $oneLogRow->occasionsID);
		$data_attrs .= sprintf(' data-ip-address="%1$s" ', esc_attr($oneLogRow->context["_server_remote_addr"]));

		// If type is single then include more details
		$more_details_html = "";
		if ($args["type"] == "single") {

			$more_details_html .= sprintf('<h2 class="SimpleHistoryLogitem__moreDetailsHeadline">%1$s</h2>', __("Context data", "simple-history"));
			$more_details_html .= "<p>" . __("This is potentially useful meta data that a logger has saved.", "simple-history") . "</p>";
			$more_details_html .= "<table class='SimpleHistoryLogitem__moreDetailsContext'>";
			$more_details_html .= sprintf(
				'<tr>
					<th>%1$s</th>
					<th>%2$s</th>
				</tr>',
				"Key",
				"Value"
			);

			foreach ($oneLogRow as $rowKey => $rowVal) {

				// skip arrays and objects and such
				if (is_array($rowVal) || is_object($rowVal)) {
					continue;
				}

				$more_details_html .= sprintf(
					'<tr>
						<td>%1$s</td>
						<td>%2$s</td>
					</tr>',
					esc_html($rowKey),
					esc_html($rowVal)
				);

			}

			foreach ($oneLogRow->context as $contextKey => $contextVal) {

				$more_details_html .= sprintf(
					'<tr>
						<td>%1$s</td>
						<td>%2$s</td>
					</tr>',
					esc_html($contextKey),
					esc_html($contextVal)
				);

			}

			$more_details_html .= "</table>";

			$more_details_html = sprintf(
				'<div class="SimpleHistoryLogitem__moreDetails">%1$s</div>',
				$more_details_html
			);

		}

		// Classes to add to log item li element
		$classes = array(
			"SimpleHistoryLogitem",
			"SimpleHistoryLogitem--loglevel-{$oneLogRow->level}",
			"SimpleHistoryLogitem--logger-{$oneLogRow->logger}",
		);

		if (isset($oneLogRow->initiator) && !empty($oneLogRow->initiator)) {
			$classes[] = "SimpleHistoryLogitem--initiator-" . esc_attr($oneLogRow->initiator);
		}

		// Always append the log level tag
		$log_level_tag_html = sprintf(
			' <span class="SimpleHistoryLogitem--logleveltag SimpleHistoryLogitem--logleveltag-%1$s">%2$s</span>',
			$oneLogRow->level,
			$this->getLogLevelTranslated($oneLogRow->level)
		);

		$plain_text_html .= $log_level_tag_html;

		/**
		 * Filter to modify classes added to item li element
		 *
		 * @since 2.0.7
		 *
		 * @param $classes Array with classes
		 */
		$classes = apply_filters("simple_history/logrowhtmloutput/classes", $classes);

		// Generate the HTML output for a row
		$output = sprintf(
			'
				<li %8$s class="%10$s">
					<div class="SimpleHistoryLogitem__firstcol">
						<div class="SimpleHistoryLogitem__senderImage">%3$s</div>
					</div>
					<div class="SimpleHistoryLogitem__secondcol">
						<div class="SimpleHistoryLogitem__header">%1$s</div>
						<div class="SimpleHistoryLogitem__text">%2$s</div>
						%6$s <!-- details_html -->
						%9$s <!-- more details html -->
						%4$s <!-- occasions -->
					</div>
				</li>
			',
			$header_html, // 1
			$plain_text_html, // 2
			$sender_image_html, // 3
			$occasions_html, // 4
			$oneLogRow->level, // 5
			$details_html, // 6
			$oneLogRow->logger, // 7
			$data_attrs, // 8 data attributes
			$more_details_html, // 9
			join(" ", $classes) // 10
		);

		// Get the main message row.
		// Should be as plain as possible, like plain text
		// but with links to for example users and posts
		#SimpleLoggerFormatter::getRowTextOutput($oneLogRow);

		// Get detailed HTML-based output
		// May include images, lists, any cool stuff needed to view
		#SimpleLoggerFormatter::getRowHTMLOutput($oneLogRow);

		return trim($output);

	}

	/**
	 * Return translated loglevel
	 *
	 * @since 2.0.14
	 * @param string $loglevel
	 * @return string translated loglevel
	 */
	function getLogLevelTranslated($loglevel) {

		$str_translated = "";

		switch ($loglevel) {

			// Lowercase
			case "emergency":
				$str_translated = _x("emergency", "Log level in gui", "simple-history");
				break;

			case "alert":
				$str_translated = _x("alert", "Log level in gui", "simple-history");
				break;

			case "critical":
				$str_translated = _x("critical", "Log level in gui", "simple-history");
				break;

			case "error":
				$str_translated = _x("error", "Log level in gui", "simple-history");
				break;

			case "warning":
				$str_translated = _x("warning", "Log level in gui", "simple-history");
				break;

			case "notice":
				$str_translated = _x("notice", "Log level in gui", "simple-history");
				break;

			case "info":
				$str_translated = _x("info", "Log level in gui", "simple-history");
				break;

			case "debug":
				$str_translated = _x("debug", "Log level in gui", "simple-history");
				break;

			// Uppercase
			case "Emergency":
				$str_translated = _x("Emergency", "Log level in gui", "simple-history");
				break;

			case "Alert":
				$str_translated = _x("Alert", "Log level in gui", "simple-history");
				break;

			case "Critical":
				$str_translated = _x("Critical", "Log level in gui", "simple-history");
				break;

			case "Error":
				$str_translated = _x("Error", "Log level in gui", "simple-history");
				break;

			case "Warning":
				$str_translated = _x("Warning", "Log level in gui", "simple-history");
				break;

			case "Notice":
				$str_translated = _x("Notice", "Log level in gui", "simple-history");
				break;

			case "Info":
				$str_translated = _x("Info", "Log level in gui", "simple-history");
				break;

			case "Debug":
				$str_translated = _x("Debug", "Log level in gui", "simple-history");
				break;

			default:
				$str_translated = $loglevel;

		}

		return $str_translated;

	}

	public function getInstantiatedLoggers() {

		return $this->instantiatedLoggers;

	}

	public function getInstantiatedLoggerBySlug($slug = "") {

		if (empty($slug)) {
			return false;
		}

		foreach ($this->getInstantiatedLoggers() as $one_logger) {

			if ($slug == $one_logger["instance"]->slug) {
				return $one_logger["instance"];
			}

		}

		return false;

	}

	/**
	 * Check which loggers a user has the right to read and return an array
	 * with all loggers they are allowed to read
	 *
	 * @param int $user_id Id of user to get loggers for. Defaults to current user id.
	 * @param string $format format to return loggers in. Default is array.
	 * @return array
	 */
	public function getLoggersThatUserCanRead($user_id = "", $format = "array") {

		$arr_loggers_user_can_view = array();

		if (!is_numeric($user_id)) {
			$user_id = get_current_user_id();
		}

		$loggers = $this->getInstantiatedLoggers();
		foreach ($loggers as $one_logger) {

			$logger_capability = $one_logger["instance"]->getCapability();

			//$arr_loggers_user_can_view = apply_filters("simple_history/loggers_user_can_read", $user_id, $arr_loggers_user_can_view);
			$user_can_read_logger = user_can($user_id, $logger_capability);
			$user_can_read_logger = apply_filters("simple_history/loggers_user_can_read/can_read_single_logger", $user_can_read_logger, $one_logger["instance"], $user_id);

			if ($user_can_read_logger) {
				$arr_loggers_user_can_view[] = $one_logger;
			}

		}

		/**
		 * Fires before Simple History does it's init stuff
		 *
		 * @since 2.0
		 *
		 * @param array $arr_loggers_user_can_view Array with loggers that user $user_id can read
		 * @param int user_id ID of user to check read capability for
		 */
		$arr_loggers_user_can_view = apply_filters("simple_history/loggers_user_can_read", $arr_loggers_user_can_view, $user_id);

		// just return array with slugs in parenthesis suitable for sql-where
		if ("sql" == $format) {

			$str_return = "(";

			foreach ($arr_loggers_user_can_view as $one_logger) {

				$str_return .= sprintf(
					'"%1$s", ',
					$one_logger["instance"]->slug
				);

			}

			$str_return = rtrim($str_return, " ,");
			$str_return .= ")";

			return $str_return;

		}

		return $arr_loggers_user_can_view;

	}

	/**
	 * Retrieve the avatar for a user who provided a user ID or email address.
	 * A modified version of the function that comes with WordPress, but we
	 * want to allow/show gravatars even if they are disabled in discussion settings
	 *
	 * @since 2.0
	 *
	 * @param string $email email address
	 * @param int $size Size of the avatar image
	 * @param string $default URL to a default image to use if no avatar is available
	 * @param string $alt Alternative text to use in image tag. Defaults to blank
	 * @return string <img> tag for the user's avatar
	 */
	function get_avatar($email, $size = '96', $default = '', $alt = false) {

		// WP setting for avatars is to show, so just use the built in function
		if ( get_option('show_avatars') ) {

			$avatar = get_avatar($email, $size, $default, $alt);

			return $avatar;

		} else {

			// WP setting for avatar was to not show, but we do it anyway, using the same code as get_avatar() would have used

			if (false === $alt) {
				$safe_alt = '';
			} else {
				$safe_alt = esc_attr($alt);
			}

			if (!is_numeric($size)) {
				$size = '96';
			}

			if (empty($default)) {
				$avatar_default = get_option('avatar_default');
				if (empty($avatar_default)) {
					$default = 'mystery';
				} else {
					$default = $avatar_default;
				}

			}

			if (!empty($email)) {
				$email_hash = md5(strtolower(trim($email)));
			}

			if (is_ssl()) {
				$host = 'https://secure.gravatar.com';
			} else {
				if (!empty($email)) {
					$host = sprintf("http://%d.gravatar.com", (hexdec($email_hash[0]) % 2));
				} else {
					$host = 'http://0.gravatar.com';
				}

			}

			if ('mystery' == $default) {
				$default = "$host/avatar/ad516503a11cd5ca435acc9bb6523536?s={$size}";
			}
			// ad516503a11cd5ca435acc9bb6523536 == md5('unknown@gravatar.com')
			elseif ('blank' == $default) {
				$default = $email ? 'blank' : includes_url('images/blank.gif');
			} elseif (!empty($email) && 'gravatar_default' == $default) {
				$default = '';
			} elseif ('gravatar_default' == $default) {
				$default = "$host/avatar/?s={$size}";
			} elseif (empty($email)) {
				$default = "$host/avatar/?d=$default&amp;s={$size}";
			} elseif (strpos($default, 'http://') === 0) {
				$default = add_query_arg('s', $size, $default);
			}

			if (!empty($email)) {
				$out = "$host/avatar/";
				$out .= $email_hash;
				$out .= '?s=' . $size;
				$out .= '&amp;d=' . urlencode($default);

				$rating = get_option('avatar_rating');
				if (!empty($rating)) {
					$out .= "&amp;r={$rating}";
				}

				$out = str_replace('&#038;', '&amp;', esc_url($out));
				$avatar = "<img alt='{$safe_alt}' src='{$out}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
			} else {
				$out = esc_url($default);
				$avatar = "<img alt='{$safe_alt}' src='{$out}' class='avatar avatar-{$size} photo avatar-default' height='{$size}' width='{$size}' />";
			}

			/**
			 * Filter the avatar to retrieve.
			 * Same filter WordPress uses
			 *
			 * @since 2.0.19
			 *
			 * @param string            $avatar      Image tag for the user's avatar.
			 * @param int|object|string $id_or_email A user ID, email address, or comment object.
			 * @param int               $size        Square avatar width and height in pixels to retrieve.
			 * @param string            $alt         Alternative text to use in the avatar image tag.
			 *                                       Default empty.
			 */
			$avatar = apply_filters( 'get_avatar', $avatar, $id_or_email, $size, $default, $alt );

			return $avatar;

		} // else

	}

	/**
	 * Quick stats above the log
	 * Uses filter "simple_history/history_page/before_gui" to output its contents
	 */
	public function output_quick_stats() {

		global $wpdb;

		// Get number of events today
		$logQuery = new SimpleHistoryLogQuery();
		$logResults = $logQuery->query(array(
			"posts_per_page" => 1,
			"date_from" => strtotime("today"),
		));

		$total_row_count = (int) $logResults["total_row_count"];

		// Get sql query for where to read only loggers current user is allowed to read/view
		$sql_loggers_in = $this->getLoggersThatUserCanRead(get_current_user_id(), "sql");

		// Get number of users today, i.e. events with wp_user as initiator
		$sql_users_today = sprintf('
			SELECT
				DISTINCT(c.value) AS user_id
				#h.id, h.logger, h.level, h.initiator, h.date
				FROM %3$s AS h
			INNER JOIN %4$s AS c
			ON c.history_id = h.id AND c.key = "_user_id"
			WHERE
				initiator = "wp_user"
				AND logger IN %1$s
				AND date > "%2$s"
			',
			$sql_loggers_in,
			date("Y-m-d H:i", strtotime("today")),
			$wpdb->prefix . SimpleHistory::DBTABLE,
			$wpdb->prefix . SimpleHistory::DBTABLE_CONTEXTS
		);

		$cache_key = "quick_stats_users_today_" . md5( serialize( $sql_loggers_in ) );
		$cache_group = "simple-history-" . $this->get_cache_incrementor();
		$results_users_today = wp_cache_get($cache_key, $cache_group );

		if ( false === $results_users_today ) {
			$results_users_today = $wpdb->get_results($sql_users_today);
			wp_cache_set($cache_key, $results_users_today, $cache_group );
		}

		$count_users_today = sizeof( $results_users_today );

		// Get number of other sources (not wp_user)
		$sql_other_sources_where = sprintf(
			'
				initiator <> "wp_user"
				AND logger IN %1$s
				AND date > "%2$s"
			',
			$sql_loggers_in,
			date("Y-m-d H:i", strtotime("today")),
			$wpdb->prefix . SimpleHistory::DBTABLE,
			$wpdb->prefix . SimpleHistory::DBTABLE_CONTEXTS
		);

		$sql_other_sources_where = apply_filters("simple_history/quick_stats_where", $sql_other_sources_where);

		$sql_other_sources = sprintf('
			SELECT
				DISTINCT(h.initiator) AS initiator
			FROM %3$s AS h
			WHERE
				%5$s
			',
			$sql_loggers_in,
			date("Y-m-d H:i", strtotime("today")),
			$wpdb->prefix . SimpleHistory::DBTABLE,
			$wpdb->prefix . SimpleHistory::DBTABLE_CONTEXTS,
			$sql_other_sources_where // 5
		);
		// sf_d($sql_other_sources, '$sql_other_sources');

		$cache_key = "quick_stats_results_other_sources_today_" . md5( serialize($sql_other_sources) );
		$results_other_sources_today = wp_cache_get($cache_key, $cache_group);

		if ( false === $results_other_sources_today ) {

			$results_other_sources_today = $wpdb->get_results($sql_other_sources);
			wp_cache_set($cache_key, $results_other_sources_today, $cache_group);
		
		}

		$count_other_sources = sizeof($results_other_sources_today);

		#sf_d($logResults, '$logResults');
		#sf_d($results_users_today, '$sql_users_today');
		#sf_d($results_other_sources_today, '$results_other_sources_today');

		?>
		<div class="SimpleHistoryQuickStats">
			<p>
				<?php

				$msg_tmpl = "";

				// No results today at all
				if ( $total_row_count == 0 ) {

					$msg_tmpl = __("No events today so far.", "simple-history");

				} else {

					/*
					Type of results
					x1 event today from 1 user.
					x1 event today from 1 source.
					3 events today from 1 user.
					x2 events today from 2 users.
					x2 events today from 1 user and 1 other source.
					x3 events today from 2 users and 1 other source.
					x3 events today from 1 user and 2 other sources.
					x4 events today from 2 users and 2 other sources.
					 */

					// A single event existed and was from a user
					// 1 event today from 1 user.
					if ( $total_row_count == 1 && $count_users_today == 1 ) {
						$msg_tmpl .= __('One event today from one user.', "simple-history");
					}

					// A single event existed and was from another source
					// 1 event today from 1 source.
					if ( $total_row_count == 1 && !$count_users_today ) {
						$msg_tmpl .= __('One event today from one source.', "simple-history");
					}

					// Multiple events from a single user
					// 3 events today from one user.
					if ( $total_row_count > 1 && $count_users_today == 1 && !$count_other_sources ) {
						$msg_tmpl .= __('%1$d events today from one user.', "simple-history");
					}

					// Multiple events from only users
					// 2 events today from 2 users.
					if ( $total_row_count > 1 && $count_users_today == $total_row_count ) {
						$msg_tmpl .= __('%1$d events today from %2$d users.', "simple-history");
					}

					// Multiple events from 1 single user and 1 single other source
					// 2 events today from 1 user and 1 other source.
					if ( $total_row_count && 1 == $count_users_today && 1 == $count_other_sources ) {
						$msg_tmpl .= __('%1$d events today from one user and one other source.', "simple-history");
					}

					// Multiple events from multple users but from only 1 single other source
					// 3 events today from 2 users and 1 other source.
					if ( $total_row_count > 1 && $count_users_today > 1 && $count_other_sources == 1 ) {
						$msg_tmpl .= __('%1$d events today from one user and one other source.', "simple-history");
					}

					// Multiple events from 1 user but from multiple  other source
					// 3 events today from 1 user and 2 other sources.
					if ( $total_row_count > 1 && 1 == $count_users_today && $count_other_sources > 1 ) {
						$msg_tmpl .= __('%1$d events today from one user and %3$d other sources.', "simple-history");
					}

					// Multiple events from multiple user and from multiple other sources
					// 4 events today from 2 users and 2 other sources.
					if ( $total_row_count > 1 && $count_users_today > 1 && $count_other_sources > 1 ) {
						$msg_tmpl .= __('%1$s events today from %2$d users and %3$d other sources.', "simple-history");
					}

				}

				// only show stats if we have something to output
				if ( $msg_tmpl ) {

					printf(
						$msg_tmpl,
						$logResults["total_row_count"], // 1
						$count_users_today, // 2
						$count_other_sources // 3
					);

					// Space between texts
					/*
				echo " ";

				// http://playground-root.ep/wp-admin/options-general.php?page=simple_history_settings_menu_slug&selected-tab=stats
				printf(
				'<a href="%1$s">View more stats</a>.',
				add_query_arg("selected-tab", "stats", menu_page_url(SimpleHistory::SETTINGS_MENU_SLUG, 0))
				);
				 */

				}

				?>
			</p>
		</div>
		<?php

	} // output_quick_stats

	/**
	 * https://www.tollmanz.com/invalidation-schemes/
	 * 
	 * @param $refresh bool
	 * @return string
	 */
	public static function get_cache_incrementor( $refresh = false ) {

		$incrementor_key = 'simple_history_incrementor';
		$incrementor_value = wp_cache_get( $incrementor_key );

		if ( false === $incrementor_value || true === $refresh ) {
			$incrementor_value = time();
			wp_cache_set( $incrementor_key, $incrementor_value );
		}

		//echo "<br>incrementor_value: $incrementor_value";
		return $incrementor_value;

	}

} // class
