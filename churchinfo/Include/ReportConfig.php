<?php
/*******************************************************************************
 *
 *  filename    : Include/ReportsConfig.php
 *  last change : 2003-03-14
 *  description : Configure report generation
 *
 *  http://www.infocentral.org/
 *  Copyright 2003 Chris Gebhardt
 *
 *  InfoCentral is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 ******************************************************************************/

// This class definition contains a bunch of configuration stuff and utitilities
// that are useful to all the reports generated by ChurchInfo

// Load the FPDF library
LoadLib_FPDF();

class ChurchInfoReport extends FPDF {
   //
   // Paper size for all PDF report documents
   // Sizes: A3, A4, A5, Letter, Legal, or a 2-element array for custom size
   //
   var $paperFormat = "Letter";
	var $leftX = 20;
	var $incrementY = 4;

   // General contact info for the church
   var $sChurchName = "Your church name";
   var $sChurchAddress = "Your church street address";
   var $sChurchCity = "Your city";
   var $sChurchState = "Your state";
   var $sChurchZip = "Your zip";
   var $sChurchPhone = "Your phone";
   var $sChurchEmail = "Your church email";

   var $sHomeAreaCode = "xxx";

   // Verbage for the tax report
   var $sTaxReport1 = "This letter shows our record of your payments for ";
   var $sTaxReport2 = "Your only goods and services received, if any, were intangible religious benefits as defined under the code of the Internal Revenue Service.";
   var $sTaxReport3 = "If you have any questions or corrections to make to this report, please contact the church at the above number during business hours, 9am to 4pm, M-F.";
   var $sTaxSigner = "<signs tax letter>";

   var $sReminder1 = "This letter shows our record of your pledge and payments for fiscal year ";
   var $sReminderSigner = "<signs reminder letter>";
   var $sReminderNoPledge = "We have not received your pledge.";
   var $sReminderNoPayments = "We have not received any payments.";

   // Verbage for the directory report
   var $sDirectoryDisclaimer1 = "Every effort was made to insure the accuracy of this directory.  If there are any errors or omissions, please contact the church office.\n\nThis directory is for the use of the people of ";
   var $sDirectoryDisclaimer2 = ", and the information contained in it may not be used for business or commercial purposes.";
   var $bDirLetterHead = "../Images/church_letterhead.png";

   function StripPhone ($phone) {
      if (substr ($phone, 0, 3) == $this->sHomeAreaCode)
         $phone = substr ($phone, 3, strlen ($phone) - 3);
      if (substr ($phone, 0, 5) == ("(" . $this->sHomeAreaCode . ")"))
         $phone = substr ($phone, 5, strlen ($phone) - 5);
      if (substr ($phone, 0, 1) == "-")
         $phone = substr ($phone, 1, strlen ($phone) - 1);
      return ($phone);
   }

	function PrintRightJustified ($x, $y, $str) {
		$iLen = strlen ($str);
		$nMoveBy = 10 - 2 * $iLen;
		$this->SetXY ($x + $nMoveBy, $y);
		$this->Write ($this->incrementY, $str);
	}

	function PrintRightJustifiedCell ($x, $y, $wid, $str) {
		$iLen = strlen ($str);
		$this->SetXY ($x, $y);
		$this->Cell ($wid, $this->incrementY, $str, 1, 0, 'R');
	}

	function WriteAt ($x, $y, $str) {
		$this->SetXY ($x, $y);
		$this->Write ($this->incrementY, $str);
	}

	function WriteAtCell ($x, $y, $wid, $str) {
		$this->SetXY ($x, $y);
		$this->Cell ($wid, 4, $str, 1);
	}

   function StartLetterPage ($fam_ID, $fam_Name, $fam_Address1, $fam_Address2, $fam_City, $fam_State, $fam_Zip, $fam_Country, $iYear) {
		$this->AddPage();

		$dateX = 170;
		$dateY = 25;

		$this->WriteAt ($dateX, $dateY, date("m/d/Y"));

		$curY = 35;

		$this->WriteAt ($this->leftX, $curY, $this->sChurchName); $curY += $this->incrementY;
		$this->WriteAt ($this->leftX, $curY, $this->sChurchAddress); $curY += $this->incrementY;
		$this->WriteAt ($this->leftX, $curY, $this->sChurchCity . ", " . $this->sChurchState . "  " . $this->sChurchZip); $curY += $this->incrementY;
		$this->WriteAt ($this->leftX, $curY, $this->sChurchPhone . "  " . $this->sChurchEmail); $curY += 2 * $this->incrementY;

		$this->WriteAt ($this->leftX, $curY, $this->MakeSalutation ($fam_ID)); $curY += $this->incrementY;
		if ($fam_Address1 != "") {
			$this->WriteAt ($this->leftX, $curY, $fam_Address1); $curY += $this->incrementY;
		}
		if ($fam_Address2 != "") {
			$this->WriteAt ($this->leftX, $curY, $fam_Address2); $curY += $this->incrementY;
		}
		$this->WriteAt ($this->leftX, $curY, $fam_City . ", " . $fam_State . "  " . $fam_Zip); $curY += $this->incrementY;
		if ($fam_Country != "" && $fam_Country != "USA") {
			$this->WriteAt ($this->leftX, $curY, $fam_Country); $curY += $this->incrementY;
		}
      return ($curY);
   }

   // MakeSalutation: this utility is used to figure out how to address a family
   // for correspondence.
	function MakeSalutation ($famID) {
		// Make it put the name if there is only one individual in the family
		// Make it put two first names and the last name when there are exactly two people in the family (e.g. "Nathaniel and Jeanette Brooks")
		// Make it put two whole names where there are exactly two people with different names (e.g. "Doug Philbrook and Karen Andrews")
		// When there are more than two people in the family I don't have any way to know which people are children, so I would have to just use the family name (e.g. "Grossman Family").
		$sSQL = "SELECT * FROM family_fam WHERE fam_ID=" . $famID;
		$rsFamInfo = RunQuery($sSQL);
		$aFam = mysql_fetch_array($rsFamInfo);
		extract ($aFam);

		$sSQL = "SELECT * FROM person_per WHERE per_fam_ID=" . $famID;
		$rsMembers = RunQuery($sSQL);
		$numMembers = mysql_num_rows ($rsMembers);

      $numChildren = 0;      
      $indNotChild = 0;
      for ($ind = 0; $ind < $numMembers; $ind++) {
		   $member = mysql_fetch_array($rsMembers);
         extract ($member);
         if ($per_fmr_ID == 3) {
            $numChildren++;
         } else {
            $aNotChildren[$indNotChild++] = $member;
         }
      }

      $numNotChildren = $numMembers - $numChildren;

		if ($numNotChildren == 1) {
         extract ($aNotChildren[0]);
			return ($per_FirstName . " " . $per_LastName);
		} else if ($numNotChildren == 2) {
			$firstMember = mysql_fetch_array($rsMembers);
         extract ($aNotChildren[0]);
			$firstFirstName = $per_FirstName;
			$firstLastName = $per_LastName;
			$secondMember = mysql_fetch_array($rsMembers);
         extract ($aNotChildren[1]);
			$secondFirstName = $per_FirstName;
			$secondLastName = $per_LastName;
			if ($firstLastName == $secondLastName) {
				return ($firstFirstName . " & " . $secondFirstName . " " . $firstLastName);
			} else {
				return ($firstFirstName . " " . $firstLastName . " & " . $secondFirstName . " " . $secondLastName);
			}
		} else {
			return ($fam_Name . " Family");
		}
	}
}

?>
