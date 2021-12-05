from cc_matching import calculate_group_spots, form_user_groups

test_users_to_interests = {
    0: {"football", "videogames", "ice hockey"},
    1: {"videogames", "drawing", "football"},
    2: {"football", "drawing", "ice hockey"},
    3: {"football",  "videogames", "ice hockey"},
    4: {"ice hockey", "drawing",  "football", "videogames"},
    5: {"ice hockey", "drawing", "football"},
    6: {"drawing",  "football", "videogames"},
    7: {"videogames",  "ice hockey", "drawing"},
    8: {"drawing", "football", "ice hockey"},
    9: {"football", "drawing", "ice hockey"},
    10: {"football", "videogames", "drawing"},
    11: {"videogames", "football", "ice hockey"},
    12: {"drawing", "football", "ice hockey", "cricket"},
}

test_users_to_less_popular_interests = {
    0: {"football", "videogames", "ice hockey", "cricket"},
    1: {"videogames", "drawing", "football"},
    2: {"football", "drawing", "ice hockey"},
    3: {"football",  "videogames", "ice hockey"},
    4: {"ice hockey", "drawing",  "football", "videogames"},
    5: {"ice hockey", "drawing", "football"},
    6: {"drawing",  "football", "videogames"},
    7: {"videogames",  "ice hockey", "drawing"},
    8: {"drawing", "football", "ice hockey"},
    9: {"football", "drawing", "ice hockey"},
    10: {"football", "videogames", "drawing"},
    11: {"videogames", "football", "ice hockey"},
    12: {"drawing", "football", "ice hockey", "cricket"},
}

test_users_to_interests_integers = {
    0: {1, 2, 3},
    1: {3, 4, 1},
    2: {2, 6, 8},
    3: {5, 8, 1},
    4: {3, 6, 5},
    5: {2, 5, 4},
    6: {2, 1, 6},
    7: {3, 5, 6},
    8: {2, 1, 5},
    9: {2, 1, 5},
    10: {1, 3, 2},
    11: {1, 2, 7},
    12: {3, 5, 4},
}

test_users_to_interests_with_no_match = {
    0: {"football", "Java", "SQL"},
    1: {"cricket", "Python", "MS Access"},
    2: {"hockey", "C#", "Oracle"},
    3: {"swimming",  "Java Script", "MariaDB"},
    4: {"ice hockey", "Vu JS",  "MongoDB"},
    5: {"soccer", "Type Script", "POSTSql"},
    6: {"basketball",  "C", "MySQL"},
    7: {"computer games",  "HTML", "SQL Server"},
    8: {"music", "CSS", "No SQL"},
    9: {"programming", "JQuery", "Json"},
    10: {"coffee", "Ruby", "CSV"},
    11: {"movies", "Machine", "Cloud DB"},
    12: {"drawing", "C++", "Centralized DB"},
}


def test_form_groups_with_minimum_persons_and_maximum_range():
    # Checking if groups are formed at least with 2 members and total size is never more than 4
    groups = form_user_groups(2, 4, test_users_to_interests, set())
    for group in sorted(groups, key=lambda g: (g.interest, g.users)):
        assert 2 <= len(group.users) <= 5


def test_groups_with_less_popular_interests():
    # Checking forming groups with less popular interests
    groups = form_user_groups(
        2, 4, test_users_to_less_popular_interests, set())
    for group in sorted(groups, key=lambda g: (g.interest, g.users)):
        if "cricket" in group.interest:
            assert False, f"Less interest item was displayed"


def test_form_groups_with_integer_interests():
    # Checking forming groups with integer interests
    try:
        form_user_groups(2, 4, test_users_to_interests_integers, set())
    except KeyError as exc:
        assert False, f"Interest as integers raised an exception {exc}"


def test_groups_with_no_matching_interests():
    # Checking forming groups when there is no common interest among users
    groups = form_user_groups(
        2, 4, test_users_to_interests_with_no_match, set())
    assert False == bool(groups)


interests_to_users = {}
for user, interests in test_users_to_interests.items():
    for interest in interests:
        interests_to_users.setdefault(interest, set()).add(user)


def test_calculate_group_spots_with_minimum_maximum_group_size():
    groups = calculate_group_spots(
        2,
        4,
        len(test_users_to_interests),
        interests_to_users
    )
    check_group_spots(groups, 2, 4)


def check_group_spots(groups, min_grp_size, max_grp_size):
    for i in groups:
        size_of_last_group = groups[i] % max_grp_size
        assert size_of_last_group != 0 and size_of_last_group < min_grp_size, f"Group was formed less than minimum size"