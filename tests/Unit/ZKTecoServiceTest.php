<?php

use App\Services\ZKTecoService;

test('builds a valid ZKTeco command header', function () {
    $service = new ZKTecoService();
    $createHeader = new ReflectionMethod(ZKTecoService::class, 'createHeader');

    $packet = $createHeader->invoke($service, ZKTecoService::CMD_CONNECT, 0, 0, 65534, '');

    expect(strlen($packet))->toBe(10);
    expect(substr($packet, 0, 2))->toBe("\x50\x50");
    expect(unpack('v', substr($packet, 2, 2))[1])->toBe(ZKTecoService::CMD_CONNECT);
});

test('extracts the session id from the ZKTeco response header', function () {
    $service = new ZKTecoService();
    $extractSessionId = new ReflectionMethod(ZKTecoService::class, 'extractSessionId');
    $response = pack('CCvvvv', 0x50, 0x50, ZKTecoService::CMD_ACK_OK, 0, 0x1234, 0x5678);

    expect($extractSessionId->invoke($service, $response))->toBe(0x1234);
});