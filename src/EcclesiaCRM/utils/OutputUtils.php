<?php

/* Philippe Logel */

namespace EcclesiaCRM\Utils;

use EcclesiaCRM\dto\SystemConfig;

class OutputUtils {

  public static function FormatDateOutput($bWithTime)
  {
      $fmt = SystemConfig::getValue("sDateFormatLong");
      $fmt_time = SystemConfig::getValue("sTimeFormat");

      $fmt = str_replace("/", " ", $fmt);
    
      $fmt = str_replace("-", " ", $fmt);
    
      $fmt = str_replace("d", "%d", $fmt);
      $fmt = str_replace("m", "%B", $fmt);
      $fmt = str_replace("Y", "%Y", $fmt);
    
      if ($bWithTime) {
          $fmt .= " ".$fmt_time;
      }
    
      return $fmt;
  }

  // Reinstated by Todd Pillars for Event Listing
  // Takes MYSQL DateTime
  // bWithtime 1 to be displayed
  public static function FormatDate($dDate, $bWithTime = false)
  {
      if ($dDate == '' || $dDate == '0000-00-00 00:00:00' || $dDate == '0000-00-00') {
          return '';
      }

      if (strlen($dDate) == 10) { // If only a date was passed append time
          $dDate = $dDate.' 12:00:00';
      }  // Use noon to avoid a shift in daylight time causing
      // a date change.

      if (strlen($dDate) != 19) {
          return '';
      }

      // Verify it is a valid date
      $sScanString = mb_substr($dDate, 0, 10);
      list($iYear, $iMonth, $iDay) = sscanf($sScanString, '%04d-%02d-%02d');

      if (!checkdate($iMonth, $iDay, $iYear)) {
          return 'Unknown';
      }

      $fmt = self::FormatDateOutput($bWithTime);
        
      setlocale(LC_ALL, SystemConfig::getValue("sLanguage"));
      return utf8_encode(strftime("$fmt", strtotime($dDate)));
  }

// Format a BirthDate
// Optionally, the separator may be specified.  Default is YEAR-MN-DY
  public static function FormatBirthDate($per_BirthYear, $per_BirthMonth, $per_BirthDay, $sSeparator, $bFlags)
  {
      if ($bFlags == 1 || $per_BirthYear == '') {  //Person Would Like their Age Hidden or BirthYear is not known.
          $birthYear = '1000';
      } else {
          $birthYear = $per_BirthYear;
      }

      if ($per_BirthMonth > 0 && $per_BirthDay > 0 && $birthYear != 1000) {
          if ($per_BirthMonth < 10) {
              $dBirthMonth = '0'.$per_BirthMonth;
          } else {
              $dBirthMonth = $per_BirthMonth;
          }
          if ($per_BirthDay < 10) {
              $dBirthDay = '0'.$per_BirthDay;
          } else {
              $dBirthDay = $per_BirthDay;
          }

          $dBirthDate = $dBirthMonth.$sSeparator.$dBirthDay;
          if (is_numeric($birthYear)) {
              $dBirthDate = $birthYear.$sSeparator.$dBirthDate;
              if (checkdate($dBirthMonth, $dBirthDay, $birthYear)) {
                  $dBirthDate = self::FormatDate($dBirthDate);
                  if (mb_substr($dBirthDate, -6, 6) == ', 1000') {
                      $dBirthDate = str_replace(', 1000', '', $dBirthDate);
                  }
              }
          }
      } elseif (is_numeric($birthYear) && $birthYear != 1000) {  //Person Would Like Their Age Hidden
          $dBirthDate = $birthYear;
      } else {
          $dBirthDate = '';
      }

      return $dBirthDate;
  }
  
  public static function BirthDate($year, $month, $day, $hideAge)
  {
      if (!is_null($day) && $day != '' &&
      !is_null($month) && $month != ''
    ) {
          $birthYear = $year;
          if ($hideAge) {
              $birthYear = 1900;
          }

          return date_create($birthYear.'-'.$month.'-'.$day);
      }

      return date_create();
  }

}

?>