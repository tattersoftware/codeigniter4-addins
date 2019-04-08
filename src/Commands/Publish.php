<?php namespace Tatter\Addins\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Publish extends BaseCommand
{
    protected $group       = 'Tatter';
    protected $name        = 'tatter:publish';
    protected $description = 'Integrates tatter/* addins with your database and /app directory (safe).';

    public function run(array $params)
    {

	}
}
