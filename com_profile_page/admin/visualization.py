# Use at least Python 3.9
#
# This demo visualization uses the `graphviz` package ( https://pypi.org/project/graphviz/ )
# You can download it with the console command:
#   pip install graphviz
#
# For the python package you'll also need the graphviz software, `dot` ( https://www.graphviz.org/download/ )

import random
import graphviz
from cc_groups import form_groups, calculate_group_spots, Interest, UserId

# Controls whether the vizualization graphs are outputted as .gv and .png files with graphviz
draw_vizualisations: bool = True

#seed = random.randrange(0, 1e10)
# print(seed)
random.seed(3573274025)  # empirically chosen seed value

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
                         "Kyung",
                         "Leah",
                         "Martin",
                         ]

test_users_to_interests: dict[UserId, set[Interest]] = {
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
    12: {"drawing", "football", "ice hockey"},
}

# create the inverse mapping
test_interests_to_users: dict[Interest, set[UserId]] = dict()
for user, interests in test_users_to_interests.items():
    for interest in interests:
        test_interests_to_users.setdefault(interest, set()).add(user)

graph = None
if draw_vizualisations:
    graph = graphviz.Digraph(
        name="Users to their interests",
        directory="demo",
        format="png",
        engine="neato",
        graph_attr={"bgcolor": "transparent", "fillcolor": "white",
                    "fontname": "Calibri Bold", "fontcolor": "#333333", "fontsize": "30.0"},
        node_attr={"style": "filled,rounded", "shape": "box", "width": "1.4", "color": "#111111", "penwidth": "2.0",
                   "fontname": "Calibri", "fontsize": "20.0"},
        edge_attr={"color": "#333333", "penwidth": "2.0",
                   "arrowsize": "1.5", "arrowhead": "empty"},
    )

    start_graph_edges = list()

    height = 8.0
    interest_y_factor = height / len(test_interests_to_users)
    user_y_factor = height / len(test_users_to_interests)
    interest_y = 0.5/interest_y_factor

    # Add the user nodes
    for i, user in zip(range(len(user_names)), user_names):
        graph.node(str(i), user,
                   fillcolor="#dcdfe0",
                   pos=f"0,{height - i * user_y_factor}!")

    # for labeling purposes
    free_spots: dict[Interest, int] = calculate_group_spots(
        2,
        2,
        len(test_users_to_interests),
        test_interests_to_users
    )

    # Add the interest nodes in alphabetical order
    for interest, users in sorted(test_interests_to_users.items()):
        graph.node(interest,
                   label=f"{interest} ({free_spots[interest]})",
                   fillcolor="#ffa599",
                   width="2.5",
                   pos=f"5,{height - interest_y * interest_y_factor}!")
        interest_y += 1.0

        # Record the interest edges
        for user in users:
            start_graph_edges.append((f"{user}:e", f"{interest}:w"))
            # graph.edge(f"{user}:e", f"{interest}:w",
            #           style="solid", color="#00000011", arrowhead="none")

    # Create a list with the starting interest edges
    start_graph = graph.copy()
    start_graph.edges(start_graph_edges)
    start_graph.edge_attr.update(arrowhead="none")
    start_graph.filename = "0-start.gv"
    start_graph.attr(label="Graph of users and interests")

    # Save the render
    start_graph.render()

groups = form_groups(2, 2, test_users_to_interests, set(), graph)

for group in sorted(groups, key=lambda g: (g.interest, g.users)):
    print(group.interest, end=": ")
    print(group.users)
