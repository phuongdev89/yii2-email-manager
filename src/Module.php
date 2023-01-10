<?php

namespace phuongdev89\email;
/**
 * @author  Alexey Samoylov <alexey.samoylov@gmail.com>
 * @author  Valentin Konusov <rlng-krsk@yandex.ru>
 *
 * Class Module
 * @package phuongdev89\email\backend
 */
class Module extends \phuongdev89\base\Module
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
