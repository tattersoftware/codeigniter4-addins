<?php namespace Tatter\Addins\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Tatter\Settings\Models\SettingModel;

class Publish extends BaseCommand
{
    protected $group       = 'Tatter';
    protected $name        = 'tatter:publish';
    protected $description = 'Integrates Tatter modules with your database and /app directory (safe to rerun).';

    public function run(array $params)
    {
		$config = new \Tatter\Addins\Config\Addins();
		$migrations = service('migrations');
		
		// Check for database connectivity
    	$db = db_connect();
    	try
    	{
    	   	$db->connect();
			$settings = service('settings');
		}
		catch (\Exception $e)
		{
			CLI::write('Warning! Could not connect to the database: ' . $e->getMessage(), 'yellow');
			CLI::write('Migrations and Settings will need to be handled later...', 'yellow');
			$db = false;
		}
		
		// If the database succeeded then setup Settings first so we can use it
		if ($db)
		{
			// Migrate the table
			$migrations->setNamespace('Tatter\\Settings');
			$migrations->latest();
		
			// Add Settings templates
			$this->seed('\Tatter\\Settings\Database\Seeds\SettingsSeeder');
		}
		
		// Merge config files
		CLI::write('Integrating config files...');
		foreach ($config->libraries as $library => $features)
		{
			if (in_array('config', $features))
			{
				$source = ROOTPATH . 'vendor/tatter/' . strtolower($library). "/bin/{$library}.php";
				if (! is_file($source))
				{
					throw new \Exception("Unable to locate config file: {$source}");
				}
				
				// check for existing file
				$path = APPPATH . "Config/{$library}.php";
				if (! is_file($path))
				{
					copy($source, $path);
				}
			}
		}
				
		// BaseController
		$source = ROOTPATH . 'vendor/tatter/addins/bin/BaseController.php';
		$sourceHash = md5_file($source);

		$path = APPPATH . 'Controllers/BaseController.php';
		if (is_file($path))
			$pathHash = md5_file($path);
		else
			$pathHash = '';
			
		$vanillaHash = ROOTPATH . 'vendor/codeigniter4/framework/app/Controllers/BaseController.php';
		
		// check if the file is missing
		$replaceFlag = false;
		if (! is_file($path))
			$replaceFlag = true;
		// check if the file is unmodified from the framework original
		elseif ($pathHash == $vanillaHash)
			$replaceFlag = true;
		
		if ($replaceFlag)
		{
			CLI::write('Replacing BaseController with library default', 'green');
			copy($source, $path);
		}

		// Alerts method
		$source = ROOTPATH . "vendor/tatter/addins/bin/header.php";
		$path = APPPATH . "Views/templates/header.php";
		if (is_file($path)):
			$contents = file_get_contents($path);
			if (! strpos($contents, "service('alerts')")):
				CLI::write('Alerts method not detected in ' . $path, 'yellow');
				CLI::write('You may want to do something like this:');
				CLI::write(file_get_contents($source), 'light_gray');
			endif;
		else:
			if (! is_dir(APPPATH . 'Views/templates')):
				mkdir(APPPATH . 'Views/templates', 0775);
			endif;
			copy($source, $path);
		endif;
		
		// Assets methods
		foreach (['header', 'footer'] as $location)
		{
			$source = ROOTPATH . "vendor/tatter/addins/bin/{$location}.php";
			$path = APPPATH . "Views/templates/{$location}.php";
			if (is_file($path)):
				$contents = file_get_contents($path);
				if (! strpos($contents, "service('assets')")):
					CLI::write('Assets methods not detected in ' . $path, 'yellow');
					CLI::write('You may want to do something like this:');
					CLI::write(file_get_contents($source), 'light_gray');
				endif;
			else:
				if (! is_dir(APPPATH . 'Views/templates')):
					mkdir(APPPATH . 'Views/templates', 0775);
				endif;
				copy($source, $path);
			endif;
		}
		
		if ($db)
		{
			// Migrations
			CLI::write('Checking migrations...');
			foreach ($config->libraries as $library => $features)
			{
				// migrations
				if ($db && in_array('migration', $features))
				{
					try {
						CLI::write("Running migrations from Tatter\\{$library}", 'green');
						$migrations->setNamespace("Tatter\\{$library}");
						$migrations->latest();
					}
					catch (\Exception $e) {
						CLI::write("Unable to migrate library '{$library}'", 'red');
					}
				}
			}

			// Check for and register any handlers
			CLI::write('Registering handlers...');
			$this->call('handlers:register');

			// Run an intial Agents check
			CLI::write('Checking agents...');
			try {
				$this->call('agents:check');
			}
			catch (\Exception $e) {
				CLI::write($e->getMessage(), 'yellow');
			}
		}
		
		CLI::write('Ready to go!');
		if ($db)
		{
			CLI::write('You may want to run some of these follow-up commands:');
			CLI::write("* php spark permits:add");
			CLI::write("* php spark settings:add");
		}
		else
		{
			CLI::write('(You may want to re-run this after setting up your database.)');
		}
	}
}
