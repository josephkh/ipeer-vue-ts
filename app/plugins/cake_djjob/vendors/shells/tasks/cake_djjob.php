<?php
// based on: https://github.com/josegonzalez/cakephp-cake-djjob/tree/1.3
// updated to php 7
App::import('Core', 'Shell');
if (!class_exists('ConnectionManager')) {
    App::import('Model', 'ConnectionManager');
}
if (!class_exists('CakeJob')) {
    App::import('Lib', 'CakeDjjob.cake_job', array(
        'file' => 'jobs' . DS . 'cake_job.php',
    ));
}
if (!class_exists('DJJob')) {
    App::import('Vendor', 'Djjob.DJJob', array(
        'file' => 'DJJob.php',
    ));
}
/**
 * CakeDjjob Task
 *
 * Wrapper around DJJob library for shells
 *
 * @copyright     Copyright 2011, Jose Diaz-Gonzalez. (http://josediazgonzalez.com)
 * @link          http://github.com/josegonzalez/cake_djjob
 * @package       cake_djjob
 * @subpackage    cake_djjob.shells.tasks
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 **/
class CakeDjjobTask extends Shell {

/**
 * Contains configuration settings for use with individual model objects.
 * Individual model settings should be stored as an associative array,
 * keyed off of the model name.
 *
 * @var array
 * @access public
 * @see Model::$alias
 */
    var $settings = array(
        'connection'=> 'default',
        'type'      => 'mysql',
    );

/**
 * Initiate CakeDjjob Task
 *
 * @param object $model
 * @param array $config
 * @return void
 * @access public
 */
    function configure($config) {
        $this->settings = array_merge($this->settings, $config);
        $connection = ConnectionManager::getDataSource('default');

        DJJob::configure([
            'driver' => 'mysql',
            'host' => $connection->config['host'],
            'dbname' => $connection->config['database'],
            'port' => $connection->config['port'],
            'user' => $connection->config['login'],
            'password' => $connection->config['password'],
        ]);
    }

/**
 * Returns a job
 *
 * Auto imports and passes through the constructor parameters to newly created job
 * Note: (PHP 5 >= 5.1.3) - requires ReflectionClass if passing arguments
 *
 * @param string $jobName Name of job being loaded
 * @param mixed $argument Some argument to pass to the job
 * @param mixed ... etc.
 * @return mixed Job instance if available, null otherwise
 */
    function load() {
        $args = func_get_args();
        if (empty($args) || !is_string($args[0])) {
            return null;
        }

        $jobName = array_shift($args);
        if (!class_exists($jobName)) {
            App::import("Lib", $jobName);
        }

        if (empty($args)) {
            return new $jobName();
        }

        if (!class_exists('ReflectionClass')) {
            return null;
        }

        $ref = new ReflectionClass($jobName);
        return $ref->newInstanceArgs($args);
    }

/**
 * Enqueues Jobs using DJJob
 *
 * Note that all Jobs enqueued using this system must extend the base CakeJob
 * class which is included in this plugin
 *
 * @param Job $job
 * @param string $queue
 * @param string $run_at
 * @return boolean True if enqueue is successful, false on failure
 */
    function enqueue($job, $queue = "default", $run_at = null) {
        return DJJob::enqueue($job, $queue, $run_at);
    }

/**
 * Bulk Enqueues Jobs using DJJob
 *
 * @param array $jobs
 * @param string $queue
 * @param string $run_at
 * @return boolean True if bulk enqueue is successful, false on failure
 */
    function bulkEnqueue($jobs, $queue = "default", $run_at = null) {
        return DJJob::bulkEnqueue($jobs, $queue, $run_at);
    }

/**
 * Returns an array containing the status of a given queue
 *
 * @param string $queue
 * @return array
 * @author Jose Diaz-Gonzalez
 **/
    function status($queue = "default") {
        return DJJob::status($queue);
    }
}