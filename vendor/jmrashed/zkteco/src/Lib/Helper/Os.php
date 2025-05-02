<?php

namespace Jmrashed\Zkteco\Lib\Helper;

use Jmrashed\Zkteco\Lib\ZKTeco;

class Os
{
    /**
     * Get the operating system information of the ZKTeco device.
     *
     * @param ZKTeco $self The instance of the ZKTeco class.
     * @return bool|mixed Returns the operating system information if successful, false otherwise.
     */
    static public function get(ZKTeco $self)
    {
        $self->_section = __METHOD__;

        $command = Util::CMD_DEVICE;
        $command_string = '~OS';

        $response = $self->_command($command, $command_string);

        // Clean the response
        if ($response !== false) {
            return str_replace($command_string . '=', '', $response);
        }

        return false;
    }
}
