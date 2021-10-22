# Use at least Python 3.9

import random
import itertools
from typing import NewType

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
    free_spots: dict[Interest, UserId],
    matchings: dict[UserId, Interest],
    matchings_inverse: dict[Interest, set[UserId]]
) -> int:
    """A function implementing a Hopcroft-Karp algorithm for creating matchings.

    Args:
        users_to_interests (dict[UserId, set[Interest]]): A mapping from users to their interests
        interests_to_users (dict[Interest, set[UserId]]): A mapping from interests to their users
        free_spots (dict[Interest, UserId]): A mapping from the interests to the amount of free group spots that it
        currently has. Will be modified.
        matchings (dict[UserId, Interest]): The current set of matchings from users to interests. Will be modified.
        matchings_inverse (dict[Interest, set[UserId]]): The inverse dict of `matchings`. Will be modified.

    Returns:
        int: The amount of matchings made. 0 means that the maximal matching was reached and the algorithm has finished.
    """

    # TODO
    pass


# The main "function"
if __name__ == "__main__":
    # TODO
    pass
