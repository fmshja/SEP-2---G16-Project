import mariadb
import sys
import json
from cc_groups import form_groups, Interest, UserId, Group

# connecting to database
try:
    conn = mariadb.connect(
        user="root",
        password="",
        host="127.0.0.1",
        port=3306,
        database="joomla_ncc"
    )
except mariadb.Error as e:
    print(f"Error connecting to MariaDB Platform: [e]")
    sys.exit(1)


# read and store the data for each interests name from the DB, for printing purposes
interest_cursor = conn.cursor(buffered=True)
interest_cursor.execute("SELECT id, interest_name FROM app_interests;")
all_interests: dict[int, str] = dict(interest_cursor)

# forming the users_to_interests dict
user_to_interest_cursor = conn.cursor(buffered=True)
user_to_interest_cursor.execute(
    "SELECT User_Id, Interest_Id FROM app_user_interests;")

users_to_interests: dict[UserId, set[Interest]] = {}
for (user_id, interests_json) in user_to_interest_cursor:
    interests: list[int] = json.loads(interests_json)
    users_to_interests[user_id] = interests


# call the function
matched_groups = form_groups(2, 2, users_to_interests, set())

# print each group
for group in matched_groups:
    print(
        f"Group of {group.users} (interest: {all_interests[group.interest]})")

# TODO: store the groups into the database
