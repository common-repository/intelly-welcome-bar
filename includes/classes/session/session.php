<?php
if(!defined('WP_SESSION_COOKIE')) {
    define('WP_SESSION_COOKIE', '_wp_session');
}
/**
 * WordPress session managment.
 *
 * Standardizes WordPress session data and uses either database transients or in-memory caching
 * for storing user session information.
 *
 * @package WordPress
 * @subpackage Session
 * @since   3.7.0
 */

/**
 * Return the current cache expire setting.
 *
 * @return int
 */
function iwb_session_cache_expire() {
	$session=IWB_Session::get_instance();
	return $session->cache_expiration();
}

/**
 * Alias of wp_session_write_close()
 */
function iwb_session_commit() {
	iwb_session_write_close();
}

/**
 * Load a JSON-encoded string into the current session.
 *
 * @param string $data
 */
function iwb_session_decode($data) {
	$session=IWB_Session::get_instance();
	return $session->json_in($data);
}

/**
 * Encode the current session's data as a JSON string.
 *
 * @return string
 */
function iwb_session_encode() {
	$session=IWB_Session::get_instance();
	return $session->json_out();
}

/**
 * Regenerate the session ID.
 *
 * @param bool $delete_old_session
 *
 * @return bool
 */
function iwb_session_regenerate_id($delete_old_session=false) {
	$session=IWB_Session::get_instance();
	$session->regenerate_id($delete_old_session);
	return true;
}

/**
 * Start new or resume existing session.
 *
 * Resumes an existing session based on a value sent by the _wp_session cookie.
 *
 * @return bool
 */
function iwb_session_start() {
	$session=IWB_Session::get_instance();
	do_action('wp_session_start');
	return $session->session_started();
}
add_action('plugins_loaded', 'iwb_session_start');

/**
 * Return the current session status.
 *
 * @return int
 */
function iwb_session_status() {
	$session=IWB_Session::get_instance();
	if ($session->session_started()) {
		return PHP_SESSION_ACTIVE;
	}
	return PHP_SESSION_NONE;
}

/**
 * Unset all session variables.
 */
function iwb_session_unset() {
	$session=IWB_Session::get_instance();
	$session->reset();
}

/**
 * Write session data and end session
 */
function iwb_session_write_close() {
	$session=IWB_Session::get_instance();
	$session->write_data();
	do_action('iwb_session_commit');
}
add_action('shutdown', 'iwb_session_write_close');

/**
 * Clean up expired sessions by removing data and their expiration entries from
 * the WordPress options table.
 *
 * This method should never be called directly and should instead be triggered as part
 * of a scheduled task or cron job.
 */
function iwb_session_cleanup() {
	global $wpdb;

	if (defined('WP_SETUP_CONFIG')) {
		return;
	}

	if (! defined('WP_INSTALLING')) {
		$expiration_keys=$wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '_wp_session_expires_%'");

		$now=time();
		$expired_sessions=array();

		foreach($expiration_keys as $expiration) {
			// If the session has expired
			if ($now > intval($expiration->option_value)) {
				// Get the session ID by parsing the option_name
				$session_id=substr($expiration->option_name, 20);

				$expired_sessions[]=$expiration->option_name;
				$expired_sessions[]="_wp_session_$session_id";
			}
		}

		// Delete all expired sessions in a single query
		if (! empty($expired_sessions)) {
			$option_names=implode("','", $expired_sessions);
			$wpdb->query("DELETE FROM $wpdb->options WHERE option_name IN ('$option_names')");
		}
	}

	// Allow other plugins to hook in to the garbage collection process.
	do_action('iwb_session_cleanup');
}
add_action('iwb_session_garbage_collection', 'iwb_session_cleanup');

/**
 * Register the garbage collector as a twice daily event.
 */
function iwb_session_register_garbage_collection() {
	if (! wp_next_scheduled('iwb_session_garbage_collection')) {
		wp_schedule_event(time(), 'hourly', 'iwb_session_garbage_collection');
	}
}
add_action('wp', 'iwb_session_register_garbage_collection');
