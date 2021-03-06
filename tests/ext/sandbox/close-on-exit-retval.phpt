--TEST--
Ensure tracing closure's $retval arg is null if invoked due to exit()
--SKIPIF--
<?php if (PHP_VERSION_ID < 70000) die('skip PHP < 7 not supported'); ?>
--FILE--
<?php
use DDTrace\SpanData;

register_shutdown_function(function () {
    $spans = dd_trace_serialize_closed_spans();
    array_map(
        function($span) {
            echo @$span['name'], PHP_EOL;
        },
        $spans
    );
});

dd_trace_function('outer', function (SpanData $span, $args, $retval) {
    $span->name =  'outer' . (isset($retval) ? ' was not null' : ' was null');
});

dd_trace_function('inner', function (SpanData $span, $args, $retval) {
    $span->name =  'inner' . (isset($retval) ? ' was not null' : ' was null');
});

function inner() { return 1; }

function outer() {
    inner();
    exit();

    // ensure we did not break something    
    return 1;
}

outer();

?>
--EXPECT--
outer was null
inner was not null
