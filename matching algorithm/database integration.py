import mariadb
import sys
from typing import NewType, Optional

UserId = NewType("UserId", int)
Interest = NewType("Interest", str)

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

# reading the interests from the database

cur.execute("SELECT u.id, interest_name FROM app_users u INNER JOIN app_user_interests ui ON u.id = ui.id_user INNER JOIN app_interests i ON id_interest = i.id;")

# forming the interests_to_users and users_to_interests dicts

interestlist = []
interests_to_users: dict[Interest, set[UserId]]
interests_to_users = {}

for (id, interest_name) in cur:
    if interest_name not in interestlist:
        interestlist.append(interest_name)

for (i) in interestlist:
    cur.scroll(-cur.rownumber)
    ids = []
    for (id, interest_name) in cur:
        if i == interest_name:
            ids.append(UserId(id))
    interests_to_users[i] = set(ids)

userlist = []
users_to_interests: dict[UserId, set[Interest]]
users_to_interests = {}

cur.scroll(-cur.rownumber)

for (id, interest_name) in cur:
    if id not in userlist:
        userlist.append(id)

for (u) in userlist:
    cur.scroll(-cur.rownumber)
    interests = []
    for (id, interest_name) in cur:
        if u == id:
            interests.append(Interest(interest_name))
    users_to_interests[u] = set(interests)
