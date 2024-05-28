SELECT
	Tmp_Act_Subsc_Sustain.Domain,
	CONCAT('<img src="https://caa-aca.ca/sustaining_subscribers/',`Tmp_Act_Subsc_Sustain`.`Logo_File`,'"') AS `Logo`,
	Tmp_Act_Subsc_Sustain.Company	
FROM Tmp_Act_Subsc_Sustain,
	users
WHERE
	Tmp_Act_Subsc_Sustain.`User ID` = users.user_id
	AND Tmp_Act_Subsc_Sustain.`Completion date` > NOW()
ORDER BY Tmp_Act_Subsc_Sustain.Company;
