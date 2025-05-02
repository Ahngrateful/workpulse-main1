<?php

namespace Jmrashed\Zkteco\Lib\Helper;

use Jmrashed\Zkteco\Lib\ZKTeco;

class Platform
{
    /**
     * Get the platform information of the ZKTeco device.
     *
     * @param ZKTeco $self The instance of the ZKTeco class.
     * @return bool|mixed Returns the platform information if successful, false otherwise.
     */
    // static public function get(ZKTeco $self)
    // {
    //     $self->_section = __METHOD__;

    //     $command = Util::CMD_DEVICE;
    //     $command_string = '~Platform';

    //     return $self->_command($command, $command_string);
    // }
    static public function get(ZKTeco $self)
    {
        $self->_section = __METHOD__;

        $command = Util::CMD_DEVICE;
        $command_string = '~Platform';

        $response = $self->_command($command, $command_string);

        // Clean the response
        if ($response !== false) {
            return str_replace($command_string.'=', '', $response);
        }

        return false;
    }


    /**
     * Get the version of the platform on the ZKTeco device.
     *
     * @param ZKTeco $self The instance of the ZKTeco class.
     * @return bool|mixed Returns the platform version if successful, false otherwise.
     */
    static public function getVersion(ZKTeco $self)
    {
        $self->_section = __METHOD__;

        $command = Util::CMD_DEVICE;
        $command_string = '~ZKFPVersion';

        $response = $self->_command($command, $command_string);

        // Clean the response
        if ($response !== false) {
            return str_replace($command_string.'=', '', $response);
        }

        return false;
    }
}
