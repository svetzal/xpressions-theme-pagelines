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
    $this->init(new DateTime($date));
  }
}

function println($str) {
  echo $str . "\n";
}

function fail($str) {
  println($str);
  die(-1);
}

function compareDateToString($date, $str) {
  $t = new DateTime($str);
  return !($t == $date);
}

function testForDate($desc, $provided, $expected) {
  println($desc . " - " . $provided . " -> " . $expected);
  $adjuster = new XprEOTAdjuster(new XprTestEOTRetriever($provided));
  if (compareDateToString($adjuster->adjustedEOT(), $expected)) fail("*** Failed renewal for $provided, expected $expected, got ".$adjuster->adjustedEOT()->format('Y-m-d'));
}

testForDate("Correctly adjusts for grace period", "2014-12-31", "2016-01-31");
testForDate("Recent renewal during Jan 2014, adjusts to CY2015", "2015-01-04", "2016-01-31");
testForDate("Mid-year signup terminates at the end of the current year", "2015-06-31", "2016-01-31");
testForDate("Mid-year signup terminates at the end of the current year", "2015-01-11", "2016-01-31");
testForDate("Mid-year signup terminates at the end of the current year", "2015-02-19", "2016-01-31");
testForDate("End-of-year signup terminates at the end of the next year", "2014-11-01", "2016-01-31");
testForDate("End-of-year signup terminates at the end of the next year", "2014-11-17", "2016-01-31");
testForDate("End-of-year signup terminates at the end of the next year", "2014-12-07", "2016-01-31");

println("Empty eot terminates at end of current year");
$adjuster = new XprEOTAdjuster(new XprTestEOTRetriever(null));
if (compareDateToString($adjuster->adjustedEOT(), "2015-01-31")) fail("*** Failed empty eot, expected 2015-01-31 got ".$adjuster->adjustedEOT()->format('Y-m-d'));
?>

