CREATE TEMPORARY TABLE `tmp_user_settings`
(	SELECT * FROM
	(	SELECT TMP1.user_id AS user_id,
	TMP1.`familyName fr` AS 'familyName fr',
	TMP1.`givenName fr` AS 'givenName fr',
	TMP1.`preferredPublicName fr` AS 'preferredPublicName fr',
	TMP1.`affiliation fr` AS 'affiliation fr',
	TMP2.familyName AS familyName,
	TMP2.givenName AS givenName,
	TMP2.preferredPublicName AS preferredPublicName,
	TMP2.affiliation AS affiliation
FROM
	(	SELECT tmp1.user_id AS user_id, 
			tmp1.setting_value AS `givenName fr`,
			tmp2.setting_value AS `familyName fr`,
			tmp3.setting_value AS `preferredPublicName fr`,
			tmp4.setting_value AS `affiliation fr`
		FROM user_settings AS tmp1
			LEFT JOIN user_settings AS tmp2
				ON tmp1.user_id = tmp2.user_id AND IF(tmp1.assoc_id = 0, tmp2.assoc_id = 0, tmp2.assoc_id IS NULL)
					AND tmp2.locale = 'fr_CA' AND tmp2.setting_name = 'familyName'
			LEFT JOIN user_settings AS tmp3
				ON tmp1.user_id = tmp3.user_id AND IF(tmp1.assoc_id = 0, tmp3.assoc_id = 0, tmp3.assoc_id IS NULL)
					AND tmp3.locale = 'fr_CA' AND tmp3.setting_name = 'preferredPublicName'
			LEFT JOIN user_settings AS tmp4
				ON tmp1.user_id = tmp4.user_id AND IF(tmp1.assoc_id = 0, tmp4.assoc_id = 0, tmp4.assoc_id IS NULL)
					AND tmp4.locale = 'fr_CA' AND tmp4.setting_name = 'affiliation'
			,
			(	SELECT user_id, COUNT(*) AS nb
				FROM user_settings
				WHERE locale = 'en_US'
	 				AND setting_name = 'givenName'
				GROUP BY user_id 
				) AS tmp_nb
		WHERE tmp1.user_id = tmp_nb.user_id
			AND tmp1.locale = 'fr_CA'
			AND tmp1.setting_name = 'givenName'
			AND IF(tmp_nb.nb = 2, tmp1.assoc_id = 0, 
				IF(tmp1.assoc_id = 0, tmp1.assoc_id = 0, tmp1.assoc_id IS NULL)
				) 
		) AS TMP1
	LEFT JOIN
	(	SELECT tmp1.user_id AS user_id, 
			tmp1.setting_value AS givenName,
			tmp2.setting_value AS familyName,
			tmp3.setting_value AS preferredPublicName,
			tmp4.setting_value AS affiliation
		FROM user_settings AS tmp1
			LEFT JOIN user_settings AS tmp2
				ON tmp1.user_id = tmp2.user_id AND IF(tmp1.assoc_id = 0, tmp2.assoc_id = 0, tmp2.assoc_id IS NULL)
					AND tmp2.locale = 'en_US' AND tmp2.setting_name = 'familyName'
			LEFT JOIN user_settings AS tmp3
				ON tmp1.user_id = tmp3.user_id AND IF(tmp1.assoc_id = 0, tmp3.assoc_id = 0, tmp3.assoc_id IS NULL)
					AND tmp3.locale = 'en_US' AND tmp3.setting_name = 'preferredPublicName'
			LEFT JOIN user_settings AS tmp4
				ON tmp1.user_id = tmp4.user_id AND IF(tmp1.assoc_id = 0, tmp4.assoc_id = 0, tmp4.assoc_id IS NULL)
					AND tmp4.locale = 'en_US' AND tmp4.setting_name = 'affiliation'
			,
			(	SELECT user_id, COUNT(*) AS nb
				FROM user_settings
				WHERE locale = 'en_US'
	 				AND setting_name = 'givenName'
				GROUP BY user_id 
				) AS tmp_nb
		WHERE tmp1.user_id = tmp_nb.user_id
			AND tmp1.locale = 'en_US'
			AND tmp1.setting_name = 'givenName'
			AND IF(tmp_nb.nb = 2, tmp1.assoc_id = 0, 
				IF(tmp1.assoc_id = 0, tmp1.assoc_id = 0, tmp1.assoc_id IS NULL)
				) 
		) AS TMP2
	ON TMP1.`user_id` = TMP2.`user_id`
UNION ALL
SELECT TMP2.user_id AS user_id,
	TMP1.`familyName fr` AS `familyName fr`,
	TMP1.`givenName fr` AS `givenName fr`,
	TMP1.`preferredPublicName fr` AS `preferredPublicName fr`,
	TMP1.`affiliation fr` AS `affiliation fr`,
	TMP2.familyName AS familyName,
	TMP2.givenName AS givenName,
	TMP2.preferredPublicName AS preferredPublicName,
	TMP2.affiliation AS affiliation
FROM
	(	SELECT tmp1.user_id AS user_id, 
			tmp1.setting_value AS `givenName fr`,
			tmp2.setting_value AS `familyName fr`,
			tmp3.setting_value AS `preferredPublicName fr`,
			tmp4.setting_value AS `affiliation fr`
		FROM user_settings AS tmp1
			LEFT JOIN user_settings AS tmp2
				ON tmp1.user_id = tmp2.user_id AND IF(tmp1.assoc_id = 0, tmp2.assoc_id = 0, tmp2.assoc_id IS NULL)
					AND tmp2.locale = 'fr_CA' AND tmp2.setting_name = 'familyName'
			LEFT JOIN user_settings AS tmp3
				ON tmp1.user_id = tmp3.user_id AND IF(tmp1.assoc_id = 0, tmp3.assoc_id = 0, tmp3.assoc_id IS NULL)
					AND tmp3.locale = 'fr_CA' AND tmp3.setting_name = 'preferredPublicName'
			LEFT JOIN user_settings AS tmp4
				ON tmp1.user_id = tmp4.user_id AND IF(tmp1.assoc_id = 0, tmp4.assoc_id = 0, tmp4.assoc_id IS NULL)
					AND tmp4.locale = 'fr_CA' AND tmp4.setting_name = 'affiliation'
			,
			(	SELECT user_id, COUNT(*) AS nb
				FROM user_settings
				WHERE locale = 'en_US'
	 				AND setting_name = 'givenName'
				GROUP BY user_id 
				) AS tmp_nb
		WHERE tmp1.user_id = tmp_nb.user_id
			AND tmp1.locale = 'fr_CA'
			AND tmp1.setting_name = 'givenName'
			AND IF(tmp_nb.nb = 2, tmp1.assoc_id = 0, 
				IF(tmp1.assoc_id = 0, tmp1.assoc_id = 0, tmp1.assoc_id IS NULL)
				) 
		) AS TMP1
	RIGHT JOIN
	(	SELECT tmp1.user_id AS user_id, 
			tmp1.setting_value AS givenName,
			tmp2.setting_value AS familyName,
			tmp3.setting_value AS preferredPublicName,
			tmp4.setting_value AS affiliation
		FROM user_settings AS tmp1
			LEFT JOIN user_settings AS tmp2
				ON tmp1.user_id = tmp2.user_id AND IF(tmp1.assoc_id = 0, tmp2.assoc_id = 0, tmp2.assoc_id IS NULL)
					AND tmp2.locale = 'en_US' AND tmp2.setting_name = 'familyName'
			LEFT JOIN user_settings AS tmp3
				ON tmp1.user_id = tmp3.user_id AND IF(tmp1.assoc_id = 0, tmp3.assoc_id = 0, tmp3.assoc_id IS NULL)
					AND tmp3.locale = 'en_US' AND tmp3.setting_name = 'preferredPublicName'
			LEFT JOIN user_settings AS tmp4
				ON tmp1.user_id = tmp4.user_id AND IF(tmp1.assoc_id = 0, tmp4.assoc_id = 0, tmp4.assoc_id IS NULL)
					AND tmp4.locale = 'en_US' AND tmp4.setting_name = 'affiliation'
			,
			(	SELECT user_id, COUNT(*) AS nb
				FROM user_settings
				WHERE locale = 'en_US'
	 				AND setting_name = 'givenName'
				GROUP BY user_id 
				) AS tmp_nb
		WHERE tmp1.user_id = tmp_nb.user_id
			AND tmp1.locale = 'en_US'
			AND tmp1.setting_name = 'givenName'
			AND IF(tmp_nb.nb = 2, tmp1.assoc_id = 0, 
				IF(tmp1.assoc_id = 0, tmp1.assoc_id = 0, tmp1.assoc_id IS NULL)
				) 
		) AS TMP2
	ON TMP1.`user_id` = TMP2.`user_id`
WHERE TMP1.`user_id` IS NULL
		) AS toto
	ORDER BY `user_id`
);