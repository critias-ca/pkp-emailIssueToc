CREATE TEMPORARY TABLE Tmp_Act_Subsc_Sustain
SELECT
    subscriptions.type_id AS 'Subscription ID',
    subscription_type_settings.setting_value AS 'Subscription Type',
    users.user_id AS 'User ID',
    IF( user_settings.preferredPublicName <> '' OR user_settings.preferredPublicName <> 0,
		user_settings.preferredPublicName,
	   	IF( user_settings.`preferredPublicName fr` <> '' OR user_settings.`preferredPublicName fr` <> 0,
			user_settings.`preferredPublicName fr`,
			IF( user_settings.givenName = '.', user_settings.familyName,
				IF( user_settings.`givenName fr` = '.', user_settings.`familyName fr`, 
					CONCAT_WS( ' ', 
						IFNULL( user_settings.givenName, IFNULL( user_settings.`givenName fr`, 'toto')), 
						IFNULL( user_settings.familyName, IFNULL( user_settings.`familyName fr`, 'titi'))
						)
					)
				)
			)
		) AS 'Contact Name',
    user_settings.familyName,
    user_settings.givenName,
    user_settings.preferredPublicName,
    user_settings.affiliation,
    user_settings.`familyName fr`,
    user_settings.`givenName fr`,
    user_settings.`preferredPublicName fr`,
    user_settings.`affiliation fr`,
    DATE_FORMAT( subscriptions.date_end, '%Y-%m-%d' ) AS 'Completion date',
    institutional_subscriptions.institution_name AS 'Company',
    REPLACE( institutional_subscriptions.domain, 'http://', '' ) AS 'Domain',
    subscriptions.reference_number AS 'Logo_File'
FROM users ,
	tmp_user_settings AS user_settings,
	subscriptions,
	subscription_type_settings,
	institutional_subscriptions
WHERE users.disabled = 0 
	AND user_settings.user_id = users.user_id
    AND subscriptions.user_id = users.user_id
    AND subscriptions.status = 1
    AND subscriptions.type_id = subscription_type_settings.type_id
    AND subscription_type_settings.locale = 'en_US'
    AND subscription_type_settings.setting_name = 'name'
	AND subscriptions.type_id = '4'
	AND subscriptions.subscription_id = institutional_subscriptions.subscription_id
ORDER BY subscriptions.type_id, users.user_id ASC;