<?php

namespace DpFixtures\JIRA;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        /** @var \Application\DeskPRO\DBAL\Connection $connection */
        $connection = $manager->getConnection();
        $connection->delete('app_packages', array('name' => 'deskpro_jira'));
        $connection->delete('app_instances', array('package_name' => 'deskpro_jira'));

        $connection->executeQuery(
<<<SQL
INSERT INTO `app_packages` (`name`, `title`, `description`, `author_name`, `author_email`, `author_link`, `api_version`, `version`, `version_name`, `native_name`, `is_single`, `is_custom`, `tags`, `settings_def`, `scopes`, `trigger_events`)
VALUES
	('deskpro_jira', 'JIRA', 'Enables agents to link DeskPRO tickets to Jira tickets.', 'DeskPRO', 'support@deskpro.com', 'https://www.deskpro.com/', 1, 1, '1.0.0', 'deskpro_jira', 1, 0, 'jira,profiles', '[{\"name\":\"url\",\"type\":\"url\",\"label\":\"Base URL\",\"description\":\"URL to your JIRA installation.\",\"native_only\":true},{\"name\":\"consumer_key\",\"type\":\"text\",\"label\":\"Application Link consumer key\",\"description\":\"Enter your JIRA Application Link consumer key.\",\"native_only\":true},{\"name\":\"private_key\",\"type\":\"text\",\"label\":\"Application Link Private Key\",\"description\":\"Enter your JIRA Application Link private key to sign API requests. Do not modify it if you want to use default private key.\",\"default\":\"-----BEGIN RSA PRIVATE KEY-----\\nMIICXgIBAAKBgQC6yzHCHz3FRHfPlXsftHBVwIpqsGMwSW338sISCUHUIk1CKOf7\\nTZX4xilU\\/XS8rsUx+hS0rhhL5DKwE9WNe6Icot0GjZYa\\/3X43H5XytHSvEgEKsqZ\\n36syXNClrrR7hs0jrCoovMzG2eRzilgBtNoMqgp9KxPWDzfyzZ4WtCnKlQIDAQAB\\nAoGAEKAPMLDpJYqfg0lRqRO9P9SgPTivy1dtwzjHDyXlxwS6jZ3ob9SK+ZZhjV\\/1\\nqOmBQZ55g09PeEm6PTO2uR384pqcAm5JwdDpXhacRXvcyqphqapihhJs2mVN9OTm\\nqhKiwXgLiBHmtdYYPaMRQJqu1W1rby3PyE0xhW9vbPZ982ECQQDnDwiOXJEC5lWP\\ntCLnJWFHqgv7LnCOSGgK9gYR\\/h4uRDWmmjeROx79CYbc366PiJrS+JSpBh4IRNZy\\ndI+05svJAkEAzvT2kRnY3kUo2lBH7lOL3l+O8J9B5cZ35Ae7WxMwepmD0itNJBLf\\nyO0xnMwfL1ltcK9PG57Ds9VIdzaAMrPWbQJBAMe5FfNAjlRMVz8dPWJBzGHO2gZn\\nsQE8EzsOSFb7KolhimKVQVz3FqUwu\\/NmIhSNxw5\\/sribwg\\/xuNA8cw+yxAkCQQCx\\nUTLcuY+VSR4qhrRKnyxsl+Uphtn4G+bm6jT9YGCd+l\\/2N4F9kepJBekHFeD2OaHJ\\n9XpLCOlWcDwJYBnQ10K1AkEAzix8lxez2lFS47c4mRU+mM7MTJCjwskn1H\\/yS2R1\\n4+kyYyGDRzjfHpEeaMRxaq7WpGhUPnfDGEcCSbJEILMLlQ==\\n-----END RSA PRIVATE KEY-----\",\"native_only\":true},{\"name\":\"public_key\",\"type\":\"text\",\"label\":\"Application Link Public Key\",\"description\":\"Copy this key to your JIRA Application Link if you want to use default JIRA Application Link Private Key.\",\"default\":\"-----BEGIN CERTIFICATE-----\\nMIICxTCCAi6gAwIBAgIJAJw9+a1S8dAqMA0GCSqGSIb3DQEBBQUAMEwxCzAJBgNV\\nBAYTAlVTMQswCQYDVQQIEwJDQTEWMBQGA1UEBxMNTW91bnRhaW4gVmlldzEYMBYG\\nA1UEAxMPd3d3LmV4YW1wbGUuY29tMB4XDTE0MTAyOTE5MDcyM1oXDTE1MTAyOTE5\\nMDcyM1owTDELMAkGA1UEBhMCVVMxCzAJBgNVBAgTAkNBMRYwFAYDVQQHEw1Nb3Vu\\ndGFpbiBWaWV3MRgwFgYDVQQDEw93d3cuZXhhbXBsZS5jb20wgZ8wDQYJKoZIhvcN\\nAQEBBQADgY0AMIGJAoGBALrLMcIfPcVEd8+Vex+0cFXAimqwYzBJbffywhIJQdQi\\nTUIo5\\/tNlfjGKVT9dLyuxTH6FLSuGEvkMrAT1Y17ohyi3QaNlhr\\/dfjcflfK0dK8\\nSAQqypnfqzJc0KWutHuGzSOsKii8zMbZ5HOKWAG02gyqCn0rE9YPN\\/LNnha0KcqV\\nAgMBAAGjga4wgaswHQYDVR0OBBYEFNb+MNAN24VEyrZX1mOVe7hN2YLFMHwGA1Ud\\nIwR1MHOAFNb+MNAN24VEyrZX1mOVe7hN2YLFoVCkTjBMMQswCQYDVQQGEwJVUzEL\\nMAkGA1UECBMCQ0ExFjAUBgNVBAcTDU1vdW50YWluIFZpZXcxGDAWBgNVBAMTD3d3\\ndy5leGFtcGxlLmNvbYIJAJw9+a1S8dAqMAwGA1UdEwQFMAMBAf8wDQYJKoZIhvcN\\nAQEFBQADgYEAWN0qMBDqssXnO7mUTUekyZf799L9ksIAC81phy5nmkeSrZbFJI+w\\nqjQQM1yTz0RiHNvP1hLtV5KkPRDb07y9UCYJRfqymdch1Vt\\/9g2tWiiI97GasNYF\\nC40w3fCzhYtwwe0duWWpHBDQ7Pn6yBNTBru3zCo2tmYsbM4fo5A\\/czE=\\n-----END CERTIFICATE-----\",\"native_only\":true}]', 'agent', '{\"update\":{\"issue_update\":\"Linked JIRA issue was updated\",\"issue_delete\":\"Linked JIRA issue was deleted\"}}');
SQL
        );

        $settings = array(
            'url' => 'https://deskpro.atlassian.net/',
            'consumer_key' => 'consumer',
            'private_key' => '-----BEGIN RSA PRIVATE KEY-----
MIICXgIBAAKBgQC6yzHCHz3FRHfPlXsftHBVwIpqsGMwSW338sISCUHUIk1CKOf7
TZX4xilU/XS8rsUx+hS0rhhL5DKwE9WNe6Icot0GjZYa/3X43H5XytHSvEgEKsqZ
36syXNClrrR7hs0jrCoovMzG2eRzilgBtNoMqgp9KxPWDzfyzZ4WtCnKlQIDAQAB
AoGAEKAPMLDpJYqfg0lRqRO9P9SgPTivy1dtwzjHDyXlxwS6jZ3ob9SK+ZZhjV/1
qOmBQZ55g09PeEm6PTO2uR384pqcAm5JwdDpXhacRXvcyqphqapihhJs2mVN9OTm
qhKiwXgLiBHmtdYYPaMRQJqu1W1rby3PyE0xhW9vbPZ982ECQQDnDwiOXJEC5lWP
tCLnJWFHqgv7LnCOSGgK9gYR/h4uRDWmmjeROx79CYbc366PiJrS+JSpBh4IRNZy
dI+05svJAkEAzvT2kRnY3kUo2lBH7lOL3l+O8J9B5cZ35Ae7WxMwepmD0itNJBLf
yO0xnMwfL1ltcK9PG57Ds9VIdzaAMrPWbQJBAMe5FfNAjlRMVz8dPWJBzGHO2gZn
sQE8EzsOSFb7KolhimKVQVz3FqUwu/NmIhSNxw5/sribwg/xuNA8cw+yxAkCQQCx
UTLcuY+VSR4qhrRKnyxsl+Uphtn4G+bm6jT9YGCd+l/2N4F9kepJBekHFeD2OaHJ
9XpLCOlWcDwJYBnQ10K1AkEAzix8lxez2lFS47c4mRU+mM7MTJCjwskn1H/yS2R1
4+kyYyGDRzjfHpEeaMRxaq7WpGhUPnfDGEcCSbJEILMLlQ==
-----END RSA PRIVATE KEY-----',
            'oauth_tokens' => array(
                'oauth_token' => 'DS5zF5IgjldsGrcq9c6yzk26D0qRFIaX',
                'oauth_token_secret' => 'YcB7S484Jm0rP8coQQv2m22CRa6Ve6bO',
                'oauth_expires_in' => 157680000,
                'oauth_session_handle' => 'UwLsvq0ciVtZ2jHUko8LpXYMi4jL1ofQ',
                'oauth_authorization_expires_in' => 160272000,
            ),
        );
        $connection->executeQuery(
<<<SQL
INSERT INTO `app_instances` (`id`, `package_name`, `title`, `secret_key`, `auth_key`, `settings`, `date_created`)
VALUES
	(32, 'deskpro_jira', 'JIRA', 'SY59FLY4202RUN2CWSKZ8MJCRO7ZCC82WGG9SF6U', 'MFY1I3L68RSH4K663RVB4BS8GNP7FKUQ7EY4CZXM', :settings, '2014-11-27 18:57:52');
SQL
        , array('settings' => json_encode($settings)));
//
//        $app = $manager->getRepository('DeskPRO:AppInstance')->findOneBy(array('package' => 'deskpro_jira'));
//        throw new \Exception(print_r($app->settings, 1));
    }
}
