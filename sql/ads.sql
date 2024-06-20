SELECT 
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
ORDER BY institutional_subscriptions.institution_name;
