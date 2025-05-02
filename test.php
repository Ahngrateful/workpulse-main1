<?php
require 'config.php';
        $zk = connectToDevice();
        if ($zk) {
            $zk->setUser('101010','101010','test','1234','14','0');

        }
        $zk->disconnect();
?>