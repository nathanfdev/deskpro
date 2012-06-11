<?php

function deskpro_handle_boot_db_exception($e)
{
	if (dp_get_config('is_installed_flag')) {
		$error_info = '';
		if (isset($_GET['show-error'])) {
			$error_info = "<hr />" . $e->getCode() . ' ' . $e->getMessage();

			$error_info = str_replace(DP_DATABASE_HOST, '...', $error_info);
			$error_info = str_replace(DP_DATABASE_NAME, '...', $error_info);
			$error_info = str_replace(DP_DATABASE_USER, '...', $error_info);
			$error_info = str_replace(DP_DATABASE_PASSWORD, '...', $error_info);

			$error_info .= "<hr />More information may be available in in data/logs/error.log";
		}

		if (class_exists('DeskPRO\Kernel\KernelErrorHandler')) {
			$e_info = \DeskPRO\Kernel\KernelErrorHandler::getExceptionInfo($e);
			\DeskPRO\Kernel\KernelErrorHandler::logToFile($e_info);
		}

		echo deskpro_install_basic_error("There was a problem connecting to the database. Please try again.$error_info", 'Error');
		exit;
	}
}
