<?php

function load_file_content(&$str = "", $fn = "")
{
	if (!is_readable($fn)) {
		error_log("error reading file: " . $fn);
		return;
	}
	$fp = @fopen($fn, "r");
	if ($fp) {
		// For each line in the file
		while (!feof($fp)) {
			// Push lines into the array
			$this_line = fgets($fp);
			if ($this_line) {
				$str .= $this_line;
			}
		}
		fclose($fp);
	} else {
		error_log("Failed to open file: " . $fn);
	}
}

function print_ads($sql, $dbhost, $dbuser, $dbpass, $dbname)
{
	$dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
	if ($dbconnect->connect_errno) {
		error_log("" . $dbconnect->connect_error);
		die("Failed to connect to MySQL: " . $dbconnect->connect_error);
	}

	$msg = "";
	$res = $dbconnect->query($sql);

	//print table content
	$ridx = 0;

	if ($res->num_rows > 0) {
		while ($row = $res->fetch_assoc()) {
			$ridx += 1;

			if (!empty($row)) {


				if ($ridx % 2 == 1)
					$odd_cls = " class=\"odd\"";
				else
					$odd_cls = "";
				$msg .= "\t<div style=\"display: inline-block; margin: 0.5rem;\"" . $odd_cls . ">\n";

				foreach ($row as $idx => $val) {
					switch ($idx) {
						case "Filename":
							$msg .= "\t\t<div>" . $val . "</div>\n";
							break;
						case "InstitutionName":
							$msg .= "\t\t<div style=\"margin-top: 1rem\">\n";
							$msg .= "\t\t\t<strong>" . $val . "</strong>\n";
							break;
					} //end of switch
				}
				$msg .= "\t\t</div>\n\t</div>\n";
			}
		}
	} else {
		error_log("No rows returned by the query.");
	}

	mysqli_free_result($res);

	$header1 = "Thanks to our ";
	$header2 = "Advertisers";
	$header1fr = "Merci à nos ";
	$header2fr = "Annonceurs";

	$msg = "<h2 style=\"margin-top: 4rem; font-weight: bold;\">" . $header1 . "<a href=\"https://caa-aca.ca/advertisement/\" target=\"_blank\">" . $header2 . "</a> | " .
		$header1fr . "<a href=\"https://caa-aca.ca/advertisement/\" target=\"_blank\">" . $header2fr . "</a></h2>\n" . $msg;

	return $msg;
}

function print_subs($sql, $dbhost, $dbuser, $dbpass, $dbname)
{
	$dbconnect = new mysqli($dbhost, $dbuser, $dbpass, $dbname);
	if ($dbconnect->connect_errno) {
		error_log("" . $dbconnect->connect_error);
		die("Failed to connect to MySQL: " . $dbconnect->connect_error);
	}
	$msg = "";
	load_file_content($TUS, __DIR__ . "/sql/tmp_user_settings.sql");
	$res_TUS = $dbconnect->query($TUS);

	load_file_content($TAS, __DIR__ . "/sql/Tmp_Act_Subsc_Sustain.sql");
	$res_TAS = $dbconnect->query($TAS);

	$res = $dbconnect->query($sql);

	//print table content
	$ridx = 0;

	while ($row = $res->fetch_assoc()) {
		$ridx += 1;

		if (!empty($row)) {


			if ($ridx % 2 == 1)
				$odd_cls = " class=\"odd\"";
			else
				$odd_cls = "";
			$msg .= "\t<div style=\"display: inline-block; margin: 0.5rem;\"" . $odd_cls . ">\n";

			$domain = false;

			foreach ($row as $idx => $val) {
				switch ($idx) {
					case "Domain":
						if (!is_null($val) and !empty($val)) {
							$domain = true;
							$msg .= "\t\t\t<a href=\"http://" . $val . "\" target=\"_blank\">";
						}
						break;
					case "Logo":
						$msg .= $val;
						break;
					case "Company":
						$msg .= " alt=\"" . $val . "\" width=\"77\"/>";
						if ($domain) {
							$msg .= "</a>";
						}
						break;
				} //end of switch

			}
			$msg .= "\t\t</div>\n";
		}
	}

	mysqli_free_result($res);

	$header1 = "Thanks to CAA";
	$header2 = "Sustaining Members";
	$header1fr = "Merci à ACA";
	$header2fr = "Membres de Soutien";

	$msg = "<h2 style=\"margin-top: 4rem; font-weight: bold;\">" . $header1 . " <a href=\"https://caa-aca.ca/membership/sustaining-subscribers/\">" . $header2 . "</a> | "
		. $header1fr . " <a href=\"https://caa-aca.ca/membership/sustaining-subscribers/\">" . $header2fr . "</a></h2>\n" . $msg;


	return $msg;

}