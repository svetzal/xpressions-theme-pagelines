<?php

/**
 * Test for EOT logic
 */

require_once(dirname(__FILE__) . '/eot-logic.php');

date_default_timezone_set("UTC");

// Mock our own retriever
class XprTestEOTRetriever extends XprFixedEOTRetriever {
  // This test class takes a string in its constructor instead of an 
  // actual date, must convert it to initialize properly
  function __construct($date) {
    if ($date) $this->init(new DateTime($date));
  }
}

function eol() {
  global $LINE_BREAK;
  if (!$LINE_BREAK) {
    if (PHP_SAPI == 'cli') {
      $LINE_BREAK = "\n";
    } else {
      $LINE_BREAK = "<br>";
    }
  }
  return $LINE_BREAK;
}

function println($str) {
  echo $str . eol();
}

function fail($str) {
  println($str);
  die(-1);
}

function compareDateToString($date, $str) {
  $t = new DateTime($str);
  return !($t == $date);
}

function valueOrNull($val) {
  return $val == null? "(null)":$val;
}

function testForDate($desc, $provided, $expected) {
  println($desc . " - " . valueOrNull($provided) . " -> " . valueOrNull($expected));
  $adjuster = new XprEOTAdjuster(new XprTestEOTRetriever($provided));
  if (compareDateToString($adjuster->adjustedEOT(), $expected)) fail("*** Failed renewal for ".valueOrNull($provided).", expected ".valueOrNull($expected).", got ".$adjuster->adjustedEOT()->format('Y-m-d'));
}

testForDate("Correctly adjusts for grace period", "2014-12-31", "2015-12-31");
testForDate("Recent renewal during Jan 2014, adjusts to CY2015", "2015-01-04", "2015-12-31");
testForDate("Mid-year signup terminates at the end of the current year", "2015-06-31", "2015-12-31");
testForDate("Early-year signup terminates at the end of the current year", "2015-01-11", "2015-12-31");
testForDate("Early current year signup terminates at the end of the current year", "2014-01-11", "2014-12-31");
testForDate("Mid-year signup terminates at the end of the current year", "2015-02-19", "2015-12-31");
testForDate("End-of-year signup terminates at the end of the next year", "2014-11-01", "2015-12-31");
testForDate("End-of-year signup terminates at the end of the next year", "2014-11-17", "2015-12-31");
testForDate("End-of-year signup terminates at the end of the next year", "2014-12-07", "2015-12-31");
testForDate("Empty eot terminates at end of 2013", null, "2013-12-31");
?>

