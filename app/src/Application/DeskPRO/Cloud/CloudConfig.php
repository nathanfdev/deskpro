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

			$stmt = $pdo->prepare("
				SELECT
					cloud_sites.*,
					cloud_accounts.id AS account_id, cloud_accounts.agents, cloud_accounts.is_demo, UNIX_TIMESTAMP(cloud_accounts.date_demo_expire) AS demo_expire_at
				FROM cloud_sites
				LEFT JOIN cloud_accounts ON cloud_accounts.cloud_site_id = cloud_sites.id
				WHERE cloud_sites.master_domain = ? OR cloud_sites.custom_domain = ?
				LIMIT 1
			");
			$stmt->execute(array($_SERVER['HTTP_HOST'], $_SERVER['HTTP_HOST']));
			$site = $stmt->fetch(\PDO::FETCH_ASSOC);
			if (!$site) {
				header('Location: ' . DP_CLOUD_MAIN_SITE);
				exit;
			}

			unset($pdo);

			define('DPC_IS_CLOUD',           true);
			define('DPC_SITE_ID',            $site['id']);
			define('DPC_ACCOUNT_ID',         $site['account_id']);
			define('DPC_AGENTS',             $site['agents']);
			define('DPC_DEMO_EXPIRE',        $site['is_demo'] ? $site['demo_expire_at'] : 0);
			define('DP_DATABASE_HOST',       $site['db_host']);
			define('DP_DATABASE_USER',       $site['db_user']);
			define('DP_DATABASE_PASSWORD',   $site['db_password']);
			define('DP_DATABASE_NAME',       $site['db_name']);
			define('DP_TECHNICAL_EMAIL',     'team@deskpro.com');
		} catch (\Exception $e) {
			self::handleException($e);
		}
	}

	public static function handleException(\Exception $e)
	{
		echo "There was a server error.<br />";
		echo $e->getMessage();
		exit;
	}
}