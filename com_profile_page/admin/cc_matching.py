"""A library which implements the group forming part of Connecting Colleagues

Requires at least Python 3.9.

The main function that you need to import and use is `form_groups`.
"""

from collections import deque
import random
import itertools
from typing import NewType, TypeVar


# Create newtypes for clarity and for the ease of potential rewriting.
UserId = NewType("UserId", int)
Interest = TypeVar("Interest", int, str)


class Group:
    """A group of users formed by the matching algorithm.

    Attributes:
        users (list[userId]): The list of users in this group
        interest (Interest): The interest that this group was formed around
    """

    def __init__(self, users: list[UserId], interest: Interest):
        self.users = users
        self.interest = interest


def shuffle(collection, rng: random.Random) -> list:
    """A helper function for shuffling collections."""
    return rng.sample(sorted(collection), len(collection))


def form_user_groups(
    min_group_size: int,
    group_size: int,
    users_to_interests: dict[UserId, set[Interest]],
    old_groups: set[Group],
    seed: int = None,
    _gv_graph=None,
) -> set[Group]:
    """Forms the groups around their interests.

    Uses a version of the Hopcroft-Karp algorithm.
    The maximum group size will be `group_size + min_group_size - 1`.

    Args:
        min_group_size (int): The smallest allowed group size
        group_size (int): The desired group size
        users_to_interests (dict[UserId, set[Interest]]): A mapping from users to their interests
        old_groups (set[Group]): The previous list of formed groups
        seed (int): The seed value that will be given to the random number generator

    Args for the visualization feature:
        _gv_graph (Optional[graphviz.Digraph]): A graphviz digraph that will be filled with data

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

    # form the inverse mapping
    interests_to_users: dict[Interest, set[UserId]] = {}
    for user, interests in users_to_interests.items():
        for interest in interests:
            interests_to_users.setdefault(interest, set()).add(user)

    # initialise the random number generator
    rng = random.Random(seed)

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

    if _gv_graph is not None:
        init_graph = _gv_graph.copy()
        init_graph.filename = "1-init match.gv"
        init_graph.attr(label="The initial matching")

    # The matchings between users and interests
    matchings: dict[UserId, Interest] = dict()
    matchings_inverse: dict[Interest, set[UserId]] = dict()

    # Get the initial matchings
    # The order that the users are iterated and the interests are picked are randomized to avoid bias
    for user, interests in shuffle(users_to_interests.items(), rng):
        for interest in shuffle(interests, rng):
            if free_spots[interest] > 0:
                matchings[user] = interest
                matchings_inverse.setdefault(interest, set()).add(user)
                free_spots[interest] -= 1

                if _gv_graph is not None:
                    init_graph.edge(f"{user}:e", f"{interest}:w")

                break

    if _gv_graph is not None:
        init_graph.render()

    ###
    # STEP 3: HOPCROFT-KARP
    ###

    if _gv_graph is not None:
        step: int = 0
        old_matchings = matchings.copy()

    # Run the algorithm until the maximal matching has been found
    while hopcroft_karp(
        users_to_interests,
        interests_to_users,
        free_spots,
        matchings,
        matchings_inverse,
    ) > 0:
        if _gv_graph is not None:
            step += 1

            step_graph = _gv_graph.copy()
            step_graph.filename = f"{step+1}-HK match.gv"
            step_graph.attr(label=f"Iteration {step} of the Hopcroft-Karp")

            for user, interest in matchings.items():
                if user in old_matchings and old_matchings[user] == interest:
                    step_graph.edge(f"{user}:e", f"{interest}:w")
                else:
                    # this changed
                    step_graph.edge(
                        f"{user}:e", f"{interest}:w", color="#ee2222")

            # check for removed edges
            for old_user, old_interest in old_matchings.items():
                if old_user in matchings and matchings[old_user] != old_interest:
                    step_graph.edge(
                        f"{old_user}:e", f"{old_interest}:w", color="#3333cc", style="dashed"
                    )

            old_matchings = matchings.copy()
            step_graph.render()

        pass

    ###
    # STEP 4: FORM GROUPS
    ###

    # The final resulting list of groups
    groups: list[Group] = list()

    # old_groups but with the interests stripped out
    old_groups: set[list[UserId]] = map(
        lambda group: group.users, iter(old_groups))

    for interest, users in matchings_inverse.items():
        # ok groups
        group_canditates: list[list[UserId]]
        # repeat groups
        group_rejects: list[list[UserId]]

        # Form groups until there's no repeat groups, or after 100 iterations
        for _ in range(0, 100):
            group_canditates = list()
            group_rejects = list()

            groups_to_check: list[list[UserId]] = list()

            for user in users:
                if len(groups_to_check) == 0 or len(groups_to_check[-1]) >= group_size:
                    # there is no users or the last group is of max size
                    groups_to_check.append([user])
                else:
                    groups_to_check[-1].append(user)

            # merge too small groups
            if len(groups_to_check) > 2 and len(groups_to_check[-1]) < min_group_size:
                small = groups_to_check.pop()
                groups_to_check[-1].extend(small)

            # check for repeat groups
            for g in groups_to_check:
                if g in old_groups:
                    # found a repeat group
                    group_rejects.append(g)
                else:
                    # group is ok
                    group_canditates.append(g)

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

    popularity_of_interest: dict[Interest, int] = dict()
    count_total: int = 0

    # Count how many users does each interest have and count the sum of those values
    for interest, users in interests_to_users.items():
        popularity_of_interest[interest] = len(users)
        count_total += len(users)

    spots_per_interest: dict[Interest, int] = dict()
    free_spots: int = number_of_users

    # Transform the amounts of people in each interest from raw counts into weight values and then to spot counts
    for interest, count in popularity_of_interest.items():
        spots: int = int(float(count) / float(count_total) * number_of_users)
        free_spots -= spots
        spots_per_interest[interest] = spots

    # Spread any remaining free spots with the interests and try to make as many of the spots for each interests
    # to be divisible by the `group_size`

    # sort by popularity
    spots_sorted = sorted(
        spots_per_interest.items(),
        key=lambda interest_count: popularity_of_interest[interest_count[0]],
        reverse=True,
    )

    for interest, count in spots_sorted:
        rem = count % group_size
        new_spots = 0

        if rem < min_group_size:
            # TODO
            pass

        if rem <= free_spots:
            # fill the remainder
            new_spots = group_size - rem

        spots_per_interest[interest] += new_spots
        free_spots -= new_spots

        if free_spots == 0:
            break

    return spots_per_interest


def hopcroft_karp(
    users_to_interests: dict[UserId, set[Interest]],
    interests_to_users: dict[Interest, set[UserId]],
    free_spots: dict[Interest, int],
    matchings: dict[UserId, Interest],
    matchings_inverse: dict[Interest, set[UserId]],
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
    if all(value <= 0 for value in free_spots.values()):
        return 0

    new_matchings: int = 0

    # Find unmatched user vertices
    # (unmatched interest vertices are in `free_spots`)
    unmatched_users: set[UserId] = set()
    for user in users_to_interests.keys():
        if user not in matchings.keys():
            unmatched_users.add(user)

    # Users to be searched by the BFS
    bfs_queue: deque[UserId] = deque(unmatched_users)
    # Users already found by the BFS
    users_visited_bfs: set[UserId] = set(unmatched_users)
    # Users removed due to them being in an augmenting path
    removed_users: set[UserId] = set()

    # The resulting list of augmenting paths
    augmenting_paths: list[list[(Interest, UserId)]] = list()

    # Create alternating level graph with a breadth-first-search
    while len(bfs_queue) != 0:
        bfs_user: UserId = bfs_queue.popleft()

        # Try to eagerly find any free spots
        spot_found: bool = False
        for interest in users_to_interests[bfs_user]:
            if free_spots[interest] > 0:
                # Found a free spot, which is a possible starting point of an augmenting path
                spot_found = True

                # Try to find an augmenting path with a depth-first-search
                path: list[(Interest, UserId)] = dfs_augmenting_path(
                    users_to_interests,
                    interests_to_users,
                    matchings_inverse,
                    unmatched_users,
                    users_visited_bfs - removed_users,
                    interest,
                )

                if len(path) > 0:
                    augmenting_paths.append(path)

                    for _, user in path:
                        # Remove the users in the path from the future dfs searches
                        removed_users.add(user)

        if not spot_found:
            # Continue the search
            for interest in users_to_interests[bfs_user]:
                to_search: set[UserId] = interests_to_users[interest] - \
                    users_visited_bfs
                bfs_queue.extend(to_search)
                users_visited_bfs.update(to_search)

    # Use the augmenting paths to create new matchings
    for path in augmenting_paths:
        failed: bool = False
        users_changed: set[UserId] = set()

        for interest, user in path:
            if user in users_changed:
                # skip users we've altered already
                continue

            users_changed.add(user)

            old_interest: Interest = matchings[user] if user in matchings else None
            matchings[user] = interest
            matchings_inverse.setdefault(interest, set()).add(user)
            free_spots[interest] -= 1

            if old_interest != None:
                matchings_inverse[old_interest].remove(user)
                free_spots[old_interest] += 1

        if not failed:  # TODO check the failing conditions
            new_matchings += 1
            if new_matchings == len(unmatched_users):
                # all found!
                break

    return new_matchings


def dfs_augmenting_path(
    users_to_interests: dict[UserId, set[Interest]],
    interests_to_users: dict[Interest, set[UserId]],
    matchings_inverse: dict[Interest, set[UserId]],
    unmatched_users: set[UserId],
    domain: set[UserId],
    search_start: Interest,
    _users_visited: set[UserId] = set(),
    _interests_visited: set[Interest] = set(),
):
    """Uses a depth-first-search to find an augmenting path from the given interest.

    Args:
        users_to_interests (dict[UserId, set[Interest]]): The mapping from users to their interests
        interests_to_users (dict[Interest, set[UserId]]): The mapping from interests to their users
        matchings_inverse (dict[Interest, set[UserId]]): The mapping of matchings from interests to users
        unmatched_users (set[UserId]): Users without matched, the potential endpoints of the path
        domain (set[UserId]): The sub-graph domain that the search will operate on
        search_start (Interest): The start node of the DFS search

    Hidden args (used in recursion):
        _users_visited (set[UserId]): The set of all users visited by this search. Will be mutated
        _interests_visited (set[Interest]): The set of all interests visited by this search. Will be mutated
    """

    _interests_visited.add(search_start)

    # Eagerly check if we found the other endpoint of the augmenting path
    for user in interests_to_users[search_start]:
        if user in unmatched_users:
            return [(search_start, user)]

    # Continue the search
    for user in interests_to_users[search_start] & domain - _users_visited:
        _users_visited.add(user)
        for interest in users_to_interests[user]:
            if interest not in _interests_visited:
                path: list[(Interest, UserId)] = dfs_augmenting_path(
                    users_to_interests,
                    interests_to_users,
                    matchings_inverse,
                    unmatched_users,
                    domain,
                    interest,
                    _users_visited,
                    _interests_visited,
                )

                if len(path) != 0:
                    # Construct the output path
                    return [(search_start, user)] + path
                else:
                    return []

    # Reached a dead end, no path was found
    return []
