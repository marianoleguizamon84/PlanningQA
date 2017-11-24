<?php

/* $Id: escapeshellarg.inc 1222 2009-10-20 11:49:41Z jberanek $
 *
 * Included if your PHP version is less than 4.0.3 and therefore this
 * function doesn't exist.
 *
 */

function escapeshellarg($x)
{
  return "'".preg_replace("/'/", "\\'", $x)."'";
} 

?>
