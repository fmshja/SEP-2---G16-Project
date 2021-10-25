# Use at least Python 3.9

# The demo visualization uses the graphviz package ( https://pypi.org/project/graphviz/ )
# You can download it with:
#   pip install graphviz
#
# You'll also need the graphviz software ( https://www.graphviz.org/download/ )

from collections import deque
import random
import itertools
from typing import NewType

import graphviz

# Create newtypes for clarity and for the ease of potential rewriting.
UserId = NewType("UserId", int)
Interest = NewType("Interest", str)


class Group:
    """A group of users formed by the matching algorithm.

    Attributes:
        users (list[userId]): The list of users in this group
        interest (Interest): The interest that this group was formed around
    """

    def __init__(self, users: list[UserId], interest: Interest):
        self.users = users
        self.interest = interest


def shuffle(collection) -> list:
    """A helper function for shuffling collections."""
    return random.sample(collection, len(collection))


def form_groups(
    min_group_size: int,
    group_size: int,
    users_to_interests: dict[UserId, set[Interest]],
    interests_to_users: dict[Interest, set[UserId]],
    old_groups: set[Group]
) -> set[Group]:
    """Forms the groups around their interests.

    Uses a version of the Hopcroft-Karp algorithm.
    The maximum group size will be `group_size + min_group_size - 1`.

    Args:
        min_group_size (int): The smallest allowed group size
        group_size (int): The desired group size
        users_to_interests (dict[UserId, set[Interest]]): A mapping from users to their interests
        interests_to_users (dict[Interest, set[UserId]]): A mapping from interests to their users
        old_groups (set[Group]): The previous list of formed groups

    Returns:
        set[Group]: The new list of formed groups, doesn't contain repeats from `old_groups`
    """

    # The function works in these steps:
    # 1. Allot Group Slots
    #     * Allot each Interest their "group slots" (how many people will form groups based on that interest)
    # 2. Create The Initial Matching
    #     * Create the first crude matching
    # 3. Hopcroft-Karp
    #     * Use Hopcroft-Karp to iteratively get better matchings
    # 4. Form Groups
    #     * Create the groups from the matching

    ###
    # STEP 1:  ALLOT GROUP SLOTS
    ###

    free_spots: dict[Interest, int] = calculate_group_spots(
        min_group_size,
        group_size,
        len(users_to_interests),
        interests_to_users
    )

    ###
    # STEP 2: CREATE THE INITIAL MATCHING
    ###

    # The matchings between users and interests
    matchings: dict[UserId, Interest] = dict()
    matchings_inverse: dict[Interest, set[UserId]] = dict()

    # Get the initial matchings
    # The order that the users are iterated and the interests are picked are randomized to avoid bias
    for user, interests in shuffle(users_to_interests):
        for interest in shuffle(interests):
            if free_spots[interest] > 0:
                matchings[user] = interest
                matchings_inverse[interest].add(user)
                free_spots[interest] -= 1

    ###
    # STEP 3: HOPCROFT-KARP
    ###

    # Run the algorithm until the maximal matching has been found
    while hopcroft_karp(users_to_interests, interests_to_users, free_spots, matchings, matchings_inverse) > 0:
        pass

    ###
    # STEP 4: FORM GROUPS
    ###

    # The final resulting list of groups
    groups: list[Group] = list()

    # old_groups but with the interests stripped out
    old_groups: set[list[UserId]] = map(
        lambda group: group.users, iter(old_groups))

    for interest, users in matchings_inverse:
        # ok groups
        group_canditates: list[list[UserId]]
        # repeat groups
        group_rejects: list[list[UserId]]

        # Form groups until there's no repeat groups, or after 100 iterations
        for _ in range(0, 100):
            group_canditates = list()
            group_rejects = list()

            groups_to_check: list[list[UserId]] = [
                users[i:i+group_size] for i in range(0, len(users), group_size)]

            if len(groups_to_check[-1]) < min_group_size:
                # merge the group with the previous one if it's too small
                small = groups_to_check.pop()
                groups_to_check[-1].append(small)

            for g in groups_to_check:
                if g in old_groups:
                    # found a repeat group
                    group_rejects.append[g]
                else:
                    # group is ok
                    group_canditates.append[g]

            # check if there were any rejects, and shuffle them into the rest to try again
            if len(group_rejects) > 0:
                for groups in group_rejects:
                    # flatten the canditates into users
                    users = list(
                        itertools.chain.from_iterable(group_canditates))
                    # shuffle the rejects back into the users
                    for reject in itertools.chain.from_iterable(group_rejects):
                        users.insert(random.randrange(len(users)), reject)
            else:
                # no repeats found
                break

        # Add the possible straggler groups if 100 iterations were reached
        group_canditates.extend(group_rejects)

        # Finally add the groups
        for g in group_canditates:
            groups.append(Group(g, interest))

    return set(groups)


def calculate_group_spots(
    min_group_size: int,
    group_size: int,
    number_of_users: int,
    interests_to_users: dict[Interest, set[UserId]]
) -> dict[Interest, int]:
    """Calculates how many group spots each interest should have.

    The total amount of spots will be exactly the same as `number_of_users`.

    Most of the spots allocated for each interest should be divisible by `group_size`,
    but their remainder should never be smaller than `min_group_size`.
    (This is to make sure that most groups will be of the desired size while no group will be too small)

    Args:
        min_group_size (int): The smallest allowed group size
        group_size (int): The desired group size
        number_of_users (int): The total amount of users are being matched
        interests_to_users (dict[Interest, set[UserId]]): A mapping from interests to their users

    Returns:
        dict[Interest, int]: A mapping from the interests to the amount of group spots that it gets
    """

    spots_per_interest: dict[Interest, int] = dict()
    count_total: int = 0

    # Count how many users does each interest have and count the sum of those values
    for interest, users in interests_to_users.items():
        spots_per_interest[interest] = len(users)
        count_total += len(users)

    free_spots: int = number_of_users

    # Transform the amounts of people in each interest from raw counts into weight values and then to spot counts
    for interest, count in spots_per_interest.items():
        spots: int = int(float(count) / float(count_total) * number_of_users)
        free_spots -= spots
        spots_per_interest[interest] = spots

    # TODO: Spread any remaining free spots with the interests and try to make as many of the spots for each interests
    # to be divisible by the `group_size` while making sure that all of their remainders will be greater or equal to
    # `min_group_size`.

    return spots_per_interest


def hopcroft_karp(
    users_to_interests: dict[UserId, set[Interest]],
    interests_to_users: dict[Interest, set[UserId]],
    free_spots: dict[Interest, int],
    matchings: dict[UserId, Interest],
    matchings_inverse: dict[Interest, set[UserId]]
) -> int:
    """A function implementing a Hopcroft-Karp algorithm for creating matchings.

    Args:
        users_to_interests (dict[UserId, set[Interest]]): A mapping from users to their interests
        interests_to_users (dict[Interest, set[UserId]]): A mapping from interests to their users
        free_spots (dict[Interest, Int]): A mapping from the interests to the amount of free group spots that it
        currently has. Will be modified.
        matchings (dict[UserId, Interest]): The current set of matchings from users to interests. Will be modified.
        matchings_inverse (dict[Interest, set[UserId]]): The inverse dict of `matchings`. Will be modified.

    Returns:
        int: The amount of matchings made. 0 means that the maximal matching was reached and the algorithm has finished.
    """

    # check if there's no spots left anymore
    if all(value == 0 for value in free_spots.values()):
        return 0

    new_matchings: int = 0

    # Find unmatched user vertices
    # (unmatched interest vertices are in `free_spots`)
    unmatched_users: set[UserId] = set()
    for user in users_to_interests.keys():
        if user not in matchings:
            unmatched_users.add(user)

    # Users to be searched by the BFS
    bfs_queue: deque[UserId] = deque(unmatched_users)
    # Users already found by the BFS
    users_visited_bfs: set[UserId] = unmatched_users
    # Users removed due to them being in an augmenting path
    removed_users: set[UserId] = set()

    # The resulting list of augmenting paths
    augmenting_paths: set[list[(Interest, UserId)]] = set()

    # Create alternating level graph with a breadth-first-search
    while len(bfs_queue) != 0:
        bfs_user: UserId = bfs_queue.popleft()

        # Try to eagerly find any free spots
        spot_found: bool = False
        for interest in users_to_interests[bfs_user]:
            # Found a free spot, which is a possible starting point of an augmenting path

            # Users visited by the DFS
            users_visited_dfs: set[UserId]

            # Try to find an augmenting path with a depth-first-search
            path: list[(Interest, UserId)] = dfs_augmenting_path(
                users_to_interests,
                interests_to_users,
                unmatched_users,
                users_visited_bfs.exclude(removed_users),
                interest,
                users_visited_dfs,
            )

            if len(path) != 0:
                augmenting_paths.add(path)

                for user in path.values():
                    # Remove the users in the path from the future dfs searches
                    removed_users.insert(user)

            spot_found = True

    if not spot_found:
        # Continue the search
        for interest in users_to_interests[bfs_user]:
            to_search: set[UserId] = interests_to_users[bfs_user] - \
                users_visited_bfs
            bfs_queue.append(to_search)
            users_visited_bfs.append(to_search)

    # Use the augmenting paths to create new matchings
    for path in augmenting_paths:
        failed: bool = False

        it, peek = itertools.tee(path)
        next(peek, None)  # ensure the peeking iterator is 1 ahead
        for interest, user in it:

            matchings[user] = interest
            matchings_inverse[interest].add(user)
            free_spots -= 1

            peeked = next(peek, None)
            if peeked != None:
                next_interest: Interest = peeked[1]
                # remove the existing matching
                matchings_inverse[next_interest].remove(user)
                free_spots[next_interest] += 1

        if not failed:  # TODO check the failing conditions
            new_matchings += 1

    return new_matchings


def dfs_augmenting_path(
    users_to_interests: dict[UserId, set[Interest]],
    interests_to_users: dict[Interest, set[UserId]],
    unmatched_users: set[UserId],
    domain: set[UserId],
    search_start: Interest,
    users_visited: set[UserId],
):
    """Uses a depth-first-search to find an augmenting path from the given interest.

    Args:
        users_to_interests (dict[UserId, set[Interest]]): A mapping from users to their interests
        interests_to_users (dict[Interest, set[UserId]]): A mapping from interests to their users
        unmatched_users (set[UserId]): Users without matched, the potential endpoints of the path
        domain (set[UserId]): The sub-graph domain that the search will operate on
        search_start (Interest): The start node of the DFS search
        users_visited (set[UserId]): The set of all users visited by this search. Will be mutated
    """

    users_visited.add(search_start)

    # Eagerly check if we found the other endpoint of the augmenting path
    for user in interests_to_users[search_start]:
        if user in unmatched_users:
            return [(search_start, user)]

    # Continue the search
    for user in interests_to_users[search_start] & domain - users_visited:
        for interest in users_to_interests[user]:
            path: list[(Interest, UserId)] = dfs_augmenting_path(
                users_to_interests,
                interests_to_users,
                unmatched_users,
                domain,
                interest,
                users_visited
            )

            if len(path) != 0:
                # Construct the output path
                return [(search_start, user)].append(path)
            else:
                return []

    # Reached a dead end, no path was found
    return []


# The main "function"
if __name__ == "__main__":
    user_names: list[str] = ["Ali",
                             "Barbara",
                             "Charlie",
                             "Dana",
                             "Eero",
                             "Fatima",
                             "Ganesh",
                             "Hilda",
                             "Ilmari",
                             "Jun",
                             ]

    test_users_to_interests: dict[UserId, set[Interest]] = {
        0: {"football", "videogames", "ice hockey", "cricket"},
        1: {"videogames", "drawing", "skiing"},
        2: {"football", "drawing", "skiing"},
        3: {"football",  "drawing", "ice hockey"},
        4: {"ice hockey", "skiing",  "football"},
        5: {"skiing", "drawing", "football"},
        6: {"skiing",  "football", "videogames", "cricket"},
        7: {"cricket", "videogames",  "ice hockey"},
        8: {"drawing", "tabletop rpg", "ice hockey"},
        9: {"football", "skiing", "drawing", "ice hockey"},
    }

    test_interests_to_users: dict[Interest, set[UserId]] = dict()

    for user, interests in test_users_to_interests.items():
        for interest in interests:
            test_interests_to_users.setdefault(interest, set()).add(user)

    graph = graphviz.Graph(
        name="Users to their interests",
        directory="demo",
        format="png",
        engine="neato",
        graph_attr={"bgcolor": "transparent"},
        node_attr={"style": "filled", "width": "1.4", "color": "#111111", "penwidth": "2.0",
                   "fontname": "Calibri", "fontsize": "20.0"},
        edge_attr={"color": "#333333", "penwidth": "2.0"},
    )
    start_graph_edges = list()

    height = 8.0
    interest_y_factor = height / len(test_interests_to_users)
    user_y_factor = height / len(test_users_to_interests)
    interest_y = 0.0

    # Add the user nodes
    for i, user in zip(range(len(user_names)), user_names):
        graph.node(str(i), user,
                   fillcolor="#dcdfe0",
                   pos=f"0,{height - i * user_y_factor}!")

    # Add the interest nodes in alphabetical order
    i2u_list = list(test_interests_to_users.items())
    i2u_list.sort()
    for interest, users in i2u_list:
        graph.node(interest,
                   fillcolor="#ffa599",
                   pos=f"5,{height - interest_y * interest_y_factor}!")
        interest_y += 1.0

        # Record the interest edges
        for user in users:
            start_graph_edges.append((f"{user}:e", f"{interest}:w"))

    # Create a list with the starting interest edges
    start_graph = graph.copy()
    start_graph.edges(start_graph_edges)
    start_graph.filename = "0-start.gv"

    # Save the render
    start_graph.render()
