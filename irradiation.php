<?php

$json = file_get_contents('data/file.json');
// $irradiation = json_decode($json, true);

echo '<pre>';
print_r($json);
echo '</pre>';

// lat, lng, jan_rad, feb_rad, mar_rad, apr_rad, may_rad, jun_rad, jul_rad, aug_rad, sep_rad, oct_rad, nov_rad, dec_rad, avg_rad