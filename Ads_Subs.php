<?php
use Illuminate\Database\Capsule\Manager;

function print_ads()
{
	$sql = "SELECT
			CASE subscriptions.type_id
				WHEN 8 THEN 'Full/'
				WHEN 13 THEN 'Half/'
				WHEN 14 THEN 'Quarter/'
			END AS 'Type',
			subscriptions.reference_number AS 'Filename',
			subscriptions.type_id  AS 'Width',
			institutional_subscriptions.institution_name AS 'InstitutionName'
		FROM subscriptions
			JOIN subscription_type_settings ON subscription_type_settings.type_id = subscriptions.type_id
			JOIN institutional_subscriptions ON subscriptions.subscription_id = institutional_subscriptions.subscription_id
			JOIN users ON subscriptions.user_id = users.user_id
		WHERE subscription_type_settings.setting_name = 'name'
			AND users.disabled = 0
			AND subscriptions.status = 1
			AND subscriptions.type_id IN (8, 13, 14)
			AND subscriptions.date_end > NOW()
		GROUP BY subscriptions.subscription_id, institutional_subscriptions.subscription_id
		ORDER BY institutional_subscriptions.institution_name";

	$msg = "";
	$res = Manager::connection()->select($sql);

	//print table content

	if (!count($res)) {
		error_log("No rows returned by the query.");
	}
	foreach ($res as $row) {
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

	$header1 = "Thanks to our ";
	$header2 = "Advertisers";
	$header1fr = "Merci à nos ";
	$header2fr = "Annonceurs";

	$msg = "<h2 style=\"margin-top: 4rem; font-weight: normal; font-family: Lato, sans-serif; letter-spacing: 0.6px;\">" . $header1 . "<a href=\"https://caa-aca.ca/advertisement/\" target=\"_blank\">" . $header2 . "</a> | " .
		$header1fr . "<a href=\"https://caa-aca.ca/advertisement/\" target=\"_blank\">" . $header2fr . "</a></h2>\n" . $msg;

	return $msg;
}

function print_subs()
{
	$sql = "SELECT
			inner_query.Domain,
			inner_query.Logo_File AS Logo,
			inner_query.Company
		FROM (
			SELECT
				REPLACE(institutional_subscriptions.domain, 'http://', '') AS 'Domain',
				subscriptions.reference_number AS 'Logo_File',
				institutional_subscriptions.institution_name AS 'Company',
				DATE_FORMAT(subscriptions.date_end, '%Y-%m-%d') AS 'Completion date'
			FROM users
			JOIN subscriptions ON subscriptions.user_id = users.user_id
			JOIN subscription_type_settings ON subscriptions.type_id = subscription_type_settings.type_id
			JOIN institutional_subscriptions ON subscriptions.subscription_id = institutional_subscriptions.subscription_id
			WHERE users.disabled = 0
				AND subscriptions.status = 1
				AND subscription_type_settings.locale = 'en_US'
				AND subscription_type_settings.setting_name = 'name'
				AND subscriptions.type_id = '4'
			ORDER BY subscriptions.type_id, users.user_id ASC
		) AS inner_query
		WHERE inner_query.`Completion date` > NOW()
		ORDER BY inner_query.Company";

	$msg = "";
	$res = Manager::connection()->select($sql);

	//print table content

	if (!count($res)) {
		error_log("No rows returned by the query.");
	}

	foreach ($res as $row) {
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

	$header1 = "Thanks to CAA";
	$header2 = "Sustaining Members";
	$header1fr = "Merci à ACA";
	$header2fr = "Membres de Soutien";

	$msg = "<h2 style=\"margin-top: 4rem; font-weight: normal; font-family: Lato, sans-serif; letter-spacing: 0.6px;\">" . $header1 . " <a href=\"https://caa-aca.ca/membership/sustaining-subscribers/\">" . $header2 . "</a> | "
		. $header1fr . " <a href=\"https://caa-aca.ca/membership/sustaining-subscribers/\">" . $header2fr . "</a></h2>\n" . $msg;

	return $msg;
}
