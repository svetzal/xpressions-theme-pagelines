<?php

/**
 * Logic to calculate End-Of-Term dates for Xpressions members
 *
 * @author Stacey Vetzal <svetzal@gmail.com>
 * @copyright 2014 Stacey Vetzal
 * @license BSD
 *
 * This code has been donated to the Xpressions group in Toronto, under 
 * the BSD license, allowing them full rights to use and modify as they 
 * see fit.
 *
 * REVISION HISTORY:
 * 2014-01-05 - initial implementation
 */

class XprFixedEOTRetriever {
  function __construct($date) {
    $this->init($date);
  }

  function init($date) {
    if ($date) $this->currentEOT = $date;
  }

  function hasNoEOT() {
    return $this->currentEOT == null;
  }
}

class XprS2MemberEOTRetriever extends XprFixedEOTRetriever {
  function __construct() {
    $this->init();
  }

  function init() {
    if (function_exists('get_user_field')) {
      $eot = get_user_field("s2member_auto_eot_time");
      if ($eot) {
        $this->currentEOT = new DateTime("@$eot"); // Convert epoch time to PHP Date object
      } else {
        $this->currentEOT = null; // s2member EOT doesn't exist for recurring memberships, administrative users
      }
    }
  }
}

class XprEOTAdjuster {
  function __construct($retriever) {
    $this->retriever = $retriever;
    $this->init();
  }

  function init() {
    $this->eot = $this->retriever->currentEOT;
    if ($this->eot) {
      $this->y = $this->eot->format('Y');
      $this->m = $this->eot->format('m');
      $this->d = $this->eot->format('d');
    }
  }

  function currentEOT() {
    return $this->eot;
  }

  // Return adjusted EOT following Xpressions membership rules
  function adjustedEOT() {
    $adjusted_eot = $this->eot;

    // If the EOT falls between January 1 and Oct 31, shorten it to the 
    // end of the current year
    if ($this->m >= 1 && $this->m <= 10)
      $adjusted_eot = $this->createDate($this->y, 12, 31);

    // If the EOT falls between November 1 and Dec 31, lengthen it to 
    // the end of the next year.
    if ($this->m >= 11)
      $adjusted_eot = $this->createDate($this->y+1, 12, 31);

    // If the EOT was blank set to end of 2013
    if (!$adjusted_eot)
      $adjusted_eot = $this->createDate(2013, 12, 31);

    return $adjusted_eot;
  }

  function adjustedEOTAsEpoch() {
    return $this->adjustedEOT()->format('U');
  }

  function createDate($Y, $m, $d) {
    return new DateTime("$Y-$m-$d"); // Create date from ISO format
  }
}

?>
