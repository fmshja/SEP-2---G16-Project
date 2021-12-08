# Connecting Colleagues

This project focuses on the improvement of the matching algorithm and user interface design of an existing application.
It is a group project for the course 'Software Engineering Project 2' of Tampere University. 

This project consists of a several Joomla components which in combination form a Connecting Colleagues website.
There is also a pair of Python scripts which form the user matches.

## Requirements
* Joomla 
* PHP 
* Python 3.9 or higher
    * The [mariadb](https://pypi.org/project/mariadb/) python library

## The components
Here are short descriptions of each Joomla component in this repository.

### Connecting home, `com_connecting_home`
TODO

### Interest, `com_interest`
TODO

### Message, `com_message`
TODO

### Profile page, `com_profile_page`
TODO

This component also has the Python script which performs the matching of users into groups of any size.
This is because currently this script, `run.py`, is currently invoked by the admin view of the profile page component.

This doesn't necessarily have to be done in this component, and it could be edited out of there and into another component, or made into its own standalone application that's run either manually or periodically.

The script itself doesn't depend on any files used in the component.
It just needs connecting colleagues python library `cc_matching.py` to be importable, and an access to a valid database.

There is also an optional file `visualization.py`, which creates a simple visualization as a series of graphviz graphs and their rendered png images.
This file requires both the [graphiz python library](https://pypi.org/project/graphviz/), and the [graphviz software, dot](https://www.graphviz.org/download/).

