from typing import NewType

# Create newtypes for clarity and for the ease of potential rewriting.
UserId = NewType("UserId", int)
Interest = NewType("Interest", str)


class Group:
    """A group of users formed by the matching algorithm.

    Attributes:
        users (set[userId]): The list of users in this group
        interest (Interest): The interest that this group was formed around
    """

    def __init__(self, users: set[UserId], interest: Interest):
        self.users = users
        self.interest = interest


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

    # TODO
    pass


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


if __name__ == "__main__":
    # TODO
    pass
