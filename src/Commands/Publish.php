<?php namespace Tatter\Addins\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

use Tatter\Settings\Models\SettingModel;

class Publish extends BaseCommand
{
    protected $group       = 'Tatter';
    protected $name        = 'tatter:publish';
    protected $description = 'Integrates tatter/* addins with your database and /app directory (safe to rerun).';

    public function run(array $params)
    {
		$config = new \Tatter\Addins\Config\Addins();
		
		// Config Files and Migrations
		$migrations = service('migrations');
		foreach ($config->libraries as $library => $features):

			// config files
			if (in_array('config', $features)):
				$source = ROOTPATH . 'vendor/tatter/codeigniter4-' . strtolower($library). "/{$library}.php.example";
				if (! is_file($source)):
					throw new \Exception("Unable to locate config file: {$source}");
				endif;
				
				// check for existing file
				$path = APPPATH . strtolower("Config/{$library}.php");
				if (! is_file($path)):
					copy($source, $path);
				endif;
			endif;
			
			// migrations
			if (in_array('migration', $features)):
				try
					$migrations->latest("\\Tatter\\{$library}");
				catch (\Exception $e)
					CLI::write("Unable to migrate library '{$library}'", 'red');
			endif;
			
		endforeach;
		
		// Settings
		$settings = service('settings');
		$settingModel = new settingModel();
		foreach ($config->settings as $name => $setting):
			// check for existing setting
			if (! $settingModel->where('name', $name)->first()):
				// create the defautl version
				$settingModel->save($setting);
			endif;
		endforeach;
		
		// BaseController
		$source = ROOTPATH . 'vendor/tatter/codeigniter4-addins/examples/BaseController.php';
		$sourceHash = md5_file($source);

		$path = APPPATH . 'Controllers/BaseController.php';
		$pathHash = md5_file($path);

		$vanillaHash = ROOTPATH . 'vendor/codeigniter4/framework/app/Controllers/BaseController.php';
		$previousHash = $settings->baseControllerHash;
		
		// check if the file is missing
		$replaceFlag = false;
		if (! is_file($path))
			$replaceFlag = true;
		// check if the file is unmodified from the original
		elseif ($pathHash == $vanillaHash)
			$replaceFlag = true;
		// check if the file is a previous version
		elseif (! empty($previousHash) && $previousHash!=$sourceHash && $pathHash==$previousHash)
			$replaceFlag = true;
		
		if ($replaceFlag)
			copy($source, $path);

		// store the hash for future runs
		$settings->baseControllerHash = $sourceHash;
		
		// BaseModel
		$source = ROOTPATH . 'vendor/tatter/codeigniter4-addins/examples/BaseModel.php';
		$sourceHash = md5_file($source);

		$path = APPPATH . 'Models/BaseModel.php';
		$pathHash = md5_file($path);

		$previousHash = $settings->baseModelHash;
		
		// check if the file is missing
		$replaceFlag = false;
		if (! is_file($path))
			$replaceFlag = true;
		// check if the file is a previous version
		elseif (! empty($previousHash) && $previousHash!=$sourceHash && $pathHash==$previousHash)
			$replaceFlag = true;
		
		if ($replaceFlag)
			copy($source, $path);

		// store the hash for future runs
		$settings->baseModelHash = $sourceHash;
		
		// Alerts method
		$source = ROOTPATH . "vendor/tatter/codeigniter4-addins/examples/header.php";
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
		foreach (['header', 'footer'] as $location):
			$source = ROOTPATH . "vendor/tatter/codeigniter4-addins/examples/{$location}.php";
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
		endif;
		
		CLI::write('Ready to go!', 'green');
		CLI::write('You may want to run some of these follow-up commands:');
		CLI::write("\t* php spark permits:add");
		CLI::write("\t* php spark settings:add");
	}
}
