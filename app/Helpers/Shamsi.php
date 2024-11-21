<?php

function shamsi($date, bool $time = true, $format = null, $lang = "fa_IR")
{
    $formatter = new IntlDateFormatter(
        "$lang@calendar=persian",
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'Asia/Tehran',
        IntlDateFormatter::TRADITIONAL,
        $format ?? ($time ? "H:m - yyyy-MM-dd" : "yyyy-MM-dd"));
    $start_date = new \DateTime($date);
    return $formatter->format($start_date);
}
