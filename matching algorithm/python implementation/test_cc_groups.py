from cc_groups import form_groups

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

test_interests_to_users = dict()
for user, interests in test_users_to_interests.items():
    for interest in interests:
        test_interests_to_users.setdefault(interest, set()).add(user)


def test_form_groups_with_minimum_persons_and_maximum_range():
    # Checking if groups are formed at least with 2 members and total size is never more than 4
    groups = form_groups(2, 4, test_users_to_interests,
                         test_interests_to_users, set())
    for group in sorted(groups, key=lambda g: (g.interest, g.users)):
        assert 2 <= len(group.users) <= 5


def form_groups_with_less_popular_interests():
    groups = form_groups(2, 4, test_users_to_less_popular_interests,
                         test_interests_to_users, set())
    for group in sorted(groups, key=lambda g: (g.interest, g.users)):
        if "cricket" in group.interest:
            print("Found")


def test_form_groups_with_integer_interests():
    try:
        form_groups(2, 4, test_users_to_interests_integers,
                    test_interests_to_users, set())
    except KeyError as exc:
        assert False, f"Interest as integers raised an exception {exc}"


# form_groups_with_less_popular_interests()
# form_groups_with_integer_interests()
