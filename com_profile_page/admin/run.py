import mariadb
import sys
import json
from cc_groups import form_groups, Interest, UserId

# y = json.loads(x)

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

cur = conn.cursor(buffered=True)
cur2 = conn.cursor(buffered=True)

# reading the interests from the database
cur.execute("SELECT id, interest_name FROM app_interests;")

# reading the interests from the database
cur2.execute("SELECT User_Id, Interest_Id FROM app_user_interests;")

# forming the interests_to_users and users_to_interests dicts
interestlist = []

users_to_interests: dict[UserId, set[Interest]] = {}
interests_to_users: dict[Interest, set[UserId]] = {}

for (User_Id, Interest_Id) in cur2:
    print(f"user id: {User_Id}, Interest id's': {Interest_Id}")
    interests = {Interest_Id}
    interests = interests.pop()
    interests2 = json.loads(interests)
    id = {User_Id}
    id = id.pop()
    id = int(id)
    print(type(id), type(interests))

    cur.execute(
        "INSERT INTO app_sort (User_Id, Interest_Id) VALUES (?, ?)",
        (id, interests))

# for (id, interest_name) in cur:
#   if interest_name not in interestlist:
#      interestlist.append(interest_name)
