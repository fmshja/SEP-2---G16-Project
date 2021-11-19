import mariadb
import sys
from typing import NewType, Optional
import json

# y = json.loads(x)

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
cur2 = conn.cursor(buffered=True)

# reading the interests from the database
cur.execute("SELECT id, interest_name FROM app_interests;")

# reading the interests from the database
cur2.execute("SELECT User_Id, Interest_Id FROM app_user_interests;")

# forming the interests_to_users and users_to_interests dicts
interestlist = []
#interests_to_users: dict[Interest, set[UserId]]
interests_to_users = {}

for (User_Id, Interest_Id) in cur2:
    print(f"user id: {User_Id}, Interest id's': {Interest_Id}")
    interests={Interest_Id}
    interests=interests.pop()
    interests2=json.loads(interests)
    id={User_Id}
    id=id.pop()
    id=int(id)
    print(type(id), type(interests))
    
    
    cur.execute(
    "INSERT INTO app_sort (id, interest) VALUES (?, ?)", 
    id, interests)

#for (id, interest_name) in cur:
#   if interest_name not in interestlist:
#      interestlist.append(interest_name)

