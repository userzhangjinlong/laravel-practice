<?php


namespace App\Enum;


class EnumMqExchangeType
{
    const TYPE_DIRECT = 'direct';
    const TYPE_FANOUT = 'fanout';
    const TYPE_TOPIC = 'topic';
    const TYPE_HEADERS = 'headers';
}
