<?php

// restrict number of ads
$inscript_google_adsense_count = 0;

function inscript_func_google_adsense (&$args)
{
  global $inscript_google_adsense_count;

  // We set this here are we always want WP formatting disabled
  $args['wp'] = 'off';

  if ($args['code'] && $inscript_google_adsense_count++ < 3)
    return $args['code'];
  return "<strong>No AdSense 'code'</strong>";
}

?>