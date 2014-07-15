#!/usr/bin/env php

<?php

// twitter date format
$date = 'Mon Jul 14 23:58:18 +0000 2014';

print_r(date_parse_from_format('D M d H:i:s O Y', $date));
