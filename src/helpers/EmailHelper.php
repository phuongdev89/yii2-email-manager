<?php
/**
 * Created by phuongdev89.
 * @author  Phuong
 * @email   phuongdev@gmail.com
 * @date    9/13/2018
 * @time    9:10 AM
 */

namespace phuongdev89\email\helpers;
class EmailHelper
{

    /**
     * @param $email
     *
     * @return string
     */
    public static function protectOff($email)
    {
        return '<!--email_off-->' . $email . '<!--/email_off-->';
    }
}
