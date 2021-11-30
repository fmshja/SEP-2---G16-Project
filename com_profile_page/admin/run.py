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


db_cursor = conn.cursor(buffered=True)

# read the data for each interests name from the DB, for printing purposes
db_cursor.execute("SELECT id, interest_name FROM app_interests;")
all_interests: dict[int, str] = dict(db_cursor)

# read the data for each user's name from the DB, for printing purposes
db_cursor.execute("SELECT id, username FROM app_users;")
all_users: dict[int, str] = dict(db_cursor)

# read the old groups
db_cursor.execute("SELECT id_group, id_user FROM app_formed_user_groups")
old_groups: dict[int, set[int]] = {}
for (group_id, user_id) in db_cursor:
    old_groups.setdefault(group_id, set()).add(user_id)
# erase group ids and convert it into a list
old_groups: list[set[int]] = list(old_groups.values())

# forming the users_to_interests dict
db_cursor.execute("SELECT User_Id, Interest_Id FROM app_user_interests;")
users_to_interests: dict[UserId, set[Interest]] = {}
for (user_id, interests_json) in db_cursor:
    interests: list[int] = json.loads(interests_json)
    users_to_interests[user_id] = interests


# call the main function which forms the groups
matched_groups = form_user_groups(2, 2, users_to_interests, old_groups)


def print_user(u): return print(f"{all_users[u]} [{u}]", end="")


# print each group
for group in matched_groups:
    users = list(group.users)
    print(f"Group of {len(users)}: ", end="")
    print_user(users[0])
    for user in users[1:-1]:
        print(", ", end="")
        print_user(user)
    if len(users) > 1:
        print(" and ", end="")
        print_user(users[-1])
    print(
        f" <i>(interest: {all_interests[group.interest]} [{group.interest}])</i>")

# delete the old groups
db_cursor.execute("DELETE FROM app_formed_user_groups;")

# store the groups into the database
for i, g in enumerate(matched_groups):
    for u in g.users:
        db_cursor.execute(
            "INSERT INTO app_formed_user_groups (id_group, id_user) VALUES (?, ?);", (i, u))

conn.commit()
