<?php
// based on: https://github.com/josegonzalez/cakephp-cake-djjob/tree/1.3
// updated to php 7

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
 * CakeDjjob Component
 *
 * Wrapper around DJJob library
 *
 * @copyright     Copyright 2011, Jose Diaz-Gonzalez. (http://josediazgonzalez.com)
 * @link          http://github.com/josegonzalez/cake_djjob
 * @package       cake_djjob
 * @subpackage    cake_djjob.controller.components
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class CakeDjjobComponent {

    var $settings = array(
        'connection'=> 'default',
        'type'      => 'mysql',
    );

/**
 * Called before the Controller::beforeFilter().
 *
 * @param object  A reference to the controller
 * @return void
 * @access public
 * @link http://book.cakephp.org/view/65/MVC-Class-Access-Within-Components
 */
    function initialize($controller = NULL, $settings = array()) {
        $this->settings = array_merge($this->settings, $settings);
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