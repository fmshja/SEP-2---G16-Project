import mariadb
import sys
import json
from cc_matching import form_user_groups, Interest, UserId

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

# read and store the data for each user's name from the DB, for printing purposes
user_cursor = conn.cursor(buffered=True)
user_cursor.execute("SELECT id, username FROM app_users;")
all_users: dict[int, str] = dict(user_cursor)

# forming the users_to_interests dict
user_to_interest_cursor = conn.cursor(buffered=True)
user_to_interest_cursor.execute(
    "SELECT User_Id, Interest_Id FROM app_user_interests;")

users_to_interests: dict[UserId, set[Interest]] = {}
for (user_id, interests_json) in user_to_interest_cursor:
    interests: list[int] = json.loads(interests_json)
    users_to_interests[user_id] = interests


# call the main function which forms the groups
matched_groups = form_user_groups(2, 2, users_to_interests, set())


def print_user(u): return print(f"{all_users[u]} [{u}]", end="")


# print each group
for group in matched_groups:
    print(f"Group of {len(group.users)}: ", end="")
    print_user(group.users[0])
    for user in group.users[1:-1]:
        print(", ", end="")
        print_user(user)
    if len(group.users) > 1:
        print(" and ", end="")
        print_user(group.users[-1])
    print(
        f" <i>(interest: {all_interests[group.interest]} [{group.interest}])</i>")

# delete the old groups
interest_cursor.execute("DELETE FROM app_formed_user_groups;")

# store the groups into the database
for i, g in enumerate(matched_groups):
    for u in g.users:
        interest_cursor.execute(
            "INSERT INTO app_formed_user_groups (id_group, id_user) VALUES (?, ?);", (i, u))

conn.commit()
