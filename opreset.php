<?php opcache_reset(); echo "OPCache reset: " . (function_exists("opcache_reset") ? "OK" : "FAIL") . PHP_EOL;
