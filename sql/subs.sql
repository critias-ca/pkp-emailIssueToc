SELECT
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
ORDER BY inner_query.Company;
