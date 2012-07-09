<?php
namespace Application\DeskPRO\Cloud;

class CloudConfig
{
	public static function loadSiteConfig()
	{
		if (empty($_SERVER['HTTP_HOST'])) {
			header('Location: ' . DP_CLOUD_MAIN_SITE);
			exit;
		}

		try {

			$pdo = new \PDO(
				sprintf("mysql:host=%s;dbname=%s", DP_CLOUD_DATABASE_HOST, DP_CLOUD_DATABASE_NAME),
				DP_CLOUD_DATABASE_USER,
				DP_CLOUD_DATABASE_PASSWORD,
				array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION)
			);

			$stmt = $pdo->prepare("SELECT * FROM cloud_sites WHERE master_domain = ? OR custom_domain = ? LIMIT 1");
			$stmt->execute(array($_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST']));
			$site = $stmt->fetch();
			if (!$site) {
				header('Location: ' . DP_CLOUD_MAIN_SITE);
				exit;
			}

			unset($pdo);

			define('DP_DATABASE_HOST',       $site['db_host']);
			define('DP_DATABASE_USER',       $site['db_user']);
			define('DP_DATABASE_PASSWORD',   $site['db_password']);
			define('DP_DATABASE_NAME',       $site['db_name']);
		} catch (\Exception $e) {
			self::handleException($e);
		}
	}

	public static function handleException(\Exception $e)
	{
		echo "There was a server error.<br />";
		echo $e->getMessage();
	}
}