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

	if ($res->num_rows > 0) {
		while ($row = $res->fetch_assoc()) {

			if (!empty($row)) {

				$msg .= "\t<div style=\"display: inline-block; margin: 0.5rem;\">\n";

				foreach ($row as $idx => $val) {
					switch ($idx) {
						case "Type":
							$msg .= '<img src="https://caa-aca.ca/advertisements/' . $val;
							break;
						case "Filename":
							$val = htmlspecialchars($val);
							$msg .= $val . "\"";
							break;
						case "Width":
							if ($val == 14) {
								$val = 77;
							} else {
								$val = 153;
							}
							$msg .= " width=\"" . $val . "\" >";
							break;
						case "InstitutionName":
							$val = $val = htmlspecialchars($val);
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

	$msg = "<h2 style=\"margin-top: 4rem; font-weight: normal; font-family: Lato, sans-serif; letter-spacing: 0.6px;\">" . $header1 . "<a href=\"https://caa-aca.ca/advertisement/\" target=\"_blank\">" . $header2 . "</a> | " .
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

	$res = $dbconnect->query($sql);

	//print table content

	while ($row = $res->fetch_assoc()) {

		if (!empty($row)) {

			$msg .= "\t<div style=\"display: inline-block; margin: 0.5rem;\">\n";

			$domain = false;

			foreach ($row as $idx => $val) {
				switch ($idx) {
					case "Domain":
						if (!is_null($val) and !empty($val)) {
							$domain = true;
							$val = htmlspecialchars($val);
							$msg .= "\t\t\t<a href=\"http://" . $val . "\" target=\"_blank\">";
						}
						break;
					case "Logo":
						$val = htmlspecialchars($val);
						$msg .= '<img src="https://caa-aca.ca/sustaining_subscribers/' . $val . "\"";
						break;
					case "Company":
						$val = htmlspecialchars($val);
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

	$msg = "<h2 style=\"margin-top: 4rem; font-weight: normal; font-family: Lato, sans-serif; letter-spacing: 0.6px;\">" . $header1 . " <a href=\"https://caa-aca.ca/membership/sustaining-subscribers/\">" . $header2 . "</a> | "
		. $header1fr . " <a href=\"https://caa-aca.ca/membership/sustaining-subscribers/\">" . $header2fr . "</a></h2>\n" . $msg;


	return $msg;

}