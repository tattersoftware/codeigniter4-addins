<?php namespace Tatter\Addins\Config;

use CodeIgniter\Config\BaseConfig;

class Addins extends BaseConfig
{
	
	// libraries and their features
	public $libraries = [
		'Assets'   => ['config'],
		'Alerts'   => ['config'],
		'Audits'   => ['config', 'migration'],
		'Permits'  => ['config', 'migration'],
		'Settings' => ['config', 'migration'],
		'Visits'   => ['config', 'migration'],
	];
	
	// default settings
	public $settings = [
			'databaseTimezone' => [
				'scope'      => 'global',
				'name'       => 'databaseTimezone',
				'content'    => 'America/New_York',
				'summary'    => 'Timezone for the database server(s)',
				'protected'  => 1,
			],
			'perPage' => [
				'scope'      => 'user',
				'name'       => 'perPage',
				'content'    => '10',
				'summary'    => 'Number of items to show per page',
				'protected'  => 1,
			],
			'theme' => [
				'scope'      => 'user',
				'name'       => 'theme',
				'content'    => 'Default',
				'summary'    => 'Site theme to use',
				'protected'  => 1,
			],
			'serverTimezone' => [
				'scope'      => 'global',
				'name'       => 'serverTimezone',
				'content'    => 'America/New_York',
				'summary'    => 'Timezone for the web server(s)',
				'protected'  => 1,
			],
			'siteVersion' => [
				'scope'      => 'global',
				'name'       => 'siteVersion',
				'content'    => '1.0.0',
				'summary'    => 'Current version of this project',
				'protected'  => 1,
			],
			'timezone' => [
				'scope'      => 'user',
				'name'       => 'timezone',
				'content'    => 'America/New_York',
				'summary'    => 'Timezone for the user',
				'protected'  => 1,
			],
			'baseControllerHash' => [
				'scope'      => 'global',
				'name'       => 'baseControllerHash',
				'content'    => '',
				'summary'    => 'Hash of BaseController from tatter/addins (to check for updates)',
				'protected'  => 0,
			],
			'baseModelHash' => [
				'scope'      => 'global',
				'name'       => 'baseModelHash',
				'content'    => '',
				'summary'    => 'Hash of BaseModel from tatter/addins (to check for updates)',
				'protected'  => 0,
			],
		];
}
