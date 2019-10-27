<?php
/**
 * Created by Navatech.
 * @project visionliberty-com
 * @author  Phuong
 * @email   notteen[at]gmail.com
 * @date    6/8/2019
 * @time    2:15 PM
 */

namespace navatech\email\interfaces;

use Throwable;

interface EmailSpoolDaemonInterface {

	/**
	 * @param $loopLimit
	 * @param $chunkSize
	 *
	 * @return mixed
	 */
	public function actionSpoolDaemon($loopLimit, $chunkSize);

	/**
	 * @return string
	 */
	public function cycle();

	/**
	 * @param $chunkSize
	 *
	 * @return bool
	 */
	public function runSpoolChunk($chunkSize);
}
