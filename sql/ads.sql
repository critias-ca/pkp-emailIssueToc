SELECT 
     CONCAT('<img src="https://caa-aca.ca/advertisements/', CASE subscriptions.type_id WHEN 8 THEN 'Full/' WHEN 13 THEN 'Half/' WHEN 14 THEN 'Quarter/' END, `subscriptions`.`reference_number`,'" alt="" width="', CASE subscriptions.type_id WHEN 14 THEN 77 ELSE 153 END, '"/>') AS 'Filename',
    institutional_subscriptions.institution_name AS 'InstitutionName'
FROM subscriptions,
    subscription_type_settings,
    institutional_subscriptions,
	users 
WHERE subscriptions.subscription_id = institutional_subscriptions.subscription_id
    AND subscription_type_settings.type_id = subscriptions.type_id
    AND subscription_type_settings.setting_name = 'name'
    AND subscriptions.user_id = users.user_id 
	AND users.disabled = 0 
	AND subscriptions.status = 1
    AND subscriptions.type_id IN (8,13,14)
	AND subscriptions.date_end > NOW()
GROUP BY subscriptions.subscription_id, institutional_subscriptions.subscription_id
ORDER BY InstitutionName