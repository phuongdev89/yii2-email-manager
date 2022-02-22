<?php

namespace phuong17889\email;
/**
 * @author  Alexey Samoylov <alexey.samoylov@gmail.com>
 * @author  Valentin Konusov <rlng-krsk@yandex.ru>
 *
 * Class Module
 * @package phuong17889\email\backend
 */
class Module extends \phuong17889\base\Module
{

    /**
     * @var int clean after days
     */
    public $cleanAfter = 30;

    /**
     * @var bool clean only body or delete the record
     */
    public $cleanOnlyBody = false;
}
