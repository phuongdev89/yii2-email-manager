<?php
/**
 * Created by Navatech.
 * @project linkyes-net
 * @author  Phuong
 * @email   notteen[at]gmail.com
 * @date    2/17/2019
 * @time    7:10 PM
 */

namespace navatech\email\traits;

use Exception;
use yii\helpers\Console;

trait EmailSpoolDaemonTrait {

	public $filetime;

	public $file;

	/**
	 * @return string
	 */
	public function cycle() {
		return 1;
	}

	/**
	 * @param bool $force_exit
	 *
	 * @return bool
	 */
	public function checkPid($force_exit = true) {
		try {
			if (file_get_contents($this->file) == $this->filetime) {
				return true;
			} else {
				Console::output('Terminated at ' . date('Y-m-d H:i:s'));
				if (!$force_exit) {
					return false;
				} else {
					exit(0);
				}
			}
		} catch (Exception $e) {
			Console::output('Terminated at ' . date('Y-m-d H:i:s'));
			if (!$force_exit) {
				return false;
			} else {
				exit(0);
			}
		}
	}
}
