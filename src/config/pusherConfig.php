<?php

function connectPusher()
{
    $options = array(
    'cluster' => 'us2',
    'useTLS' => true
  );
    $pusher = new Pusher\Pusher(
    'e800d1befb580b1b5646',
    '36111cedbe1822866159',
    '727572',
    $options
  );
    return $pusher;
}
