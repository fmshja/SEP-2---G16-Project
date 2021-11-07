--users_to_interests (strings as interests)
SELECT u.id, interest_name FROM app_users u INNER JOIN app_user_interests ui ON u.id = ui.id_user INNER JOIN app_interests i ON id_interest = i.id;
--users_to_interests (numerical id:s as interests) 
SELECT u.id, id_interest FROM app_users u INNER JOIN app_user_interests ui ON u.id = ui.id_user;
--interests_to_users (strings as interests)
SELECT interest_name, u.id FROM app_interests i INNER JOIN app_user_interests ui ON i.id = ui.id_interest INNER JOIN app_users u ON ui.id_user = u.id;
--interests_to_users (numerical id:s as interests)
SELECT ui.id_interest, u.id FROM app_user_interests ui INNER JOIN app_users u ON ui.id_user = u.id;