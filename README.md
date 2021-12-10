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

## The Database

The database runs on MariaDB and is provided by the server environment, such as Wampserver. While installing Wampserver (or other such server), make sure to select MariaDB as the database. You also need the mariadb python library for running the algorithm, which can be acquired by calling `pip install mariadb`. During the installation of Joomla, you should choose `_ncc` as the database prefix. The database structure can be viewed from `database with foreign keys` folder from the picture `nokia_sql_diagram.png`

Dependencies:

* MariaDB selected as the servers database

Setting up the database goes as such:
1. Navigate to http://localhost/phpmyadmin/
2. Log in, the default username is `root` and the password is empty, if nothing else was selected during the install.
3. Open the SQL tab on the top navigation bar.
4. From the folder `database with foreign keys`, paste the contents of the `nokia_db_creation.sql` to the servers SQL prompt and run. The server should return an empty result set.
5. The database should now be operable. If you wish to use the test data we created, run the contents of the `test_data.sql` from the folder on the SQL prompt.
## The components
Here are short descriptions of each Joomla component in this repository.

### Connecting home, `com_connecting_home`
This component serves as the front page for the Connecting Colleagues website. This page greets the user and they can log in or create a new account here. Logging in or finishing user registration should redirect user to the profile page. Content can also be showcased on this page. 

Dependencies:
* None

Installation:
1. Unzip com_connecting_home-folder.
2. Move contents of the zip-folder to the Joomla install-folder.
3. Navigate to System Extensions:install in Joomla.
4. Select "Install from Folder".
5. Click "check & install".

### Interest, `com_interest`
This component handles user-interest changing. User selects three interests when they create an account. This pages functionality is to change them and store the changes to the database. When this component is accessed from the administration side, the admin can add new interest and interest categories for users to select. This component should only be visible to the logged in users.

Dependencies:
* Table 'app_user_interests'
* Table 'app_interests'
* Table 'app_interests_groups'

Installation:
1. Unzip com_interests.
2. Move contents of the zip-folder to the Joomla install-folder.
3. Navigate to System Extensions:install in Joomla.
4. Select "Install from Folder".
5. Click "check & install".

### Message, `com_message`
This component houses the insite-messaging functionality. Once the matching algorithm is run the app_formed_user_groups-table is populated with matched user groups. User can send messages to other users who are matched in the same group as them here. This component should only be visible to the logged in users.

Dependencies:
* Table 'app_users'
* Table 'app_messages'
* Table 'app_formed_user_groups'

Installation:
1. Unzip com_message.
2. Move contents of the zip-folder to the Joomla install-folder.
3. Navigate to System Extensions:install in Joomla.
4. Select "Install from Folder".
5. Click "check & install".

### Profile page, `com_profile_page`
This component houses the profile page functionality. After user registration and log in, the user is redirected here. If user is redirected here fresh out of registration, they are then required to submit some additional information here. This information is used to initialize some other site functionalities. 

This component also has the Python script which performs the matching of users into groups of any size.
This is because currently this script, `run.py`, is currently invoked by the admin view of the profile page component.

This doesn't necessarily have to be done in this component, and it could be edited out of there and into another component, or made into its own standalone application that's run either manually or periodically.

The script itself doesn't depend on any files used in the component.
It just needs connecting colleagues python library `cc_matching.py` to be importable, and an access to a valid database.

There is also an optional file `visualization.py`, which creates a simple visualization as a series of graphviz graphs and their rendered png images.
This file requires both the [graphiz python library](https://pypi.org/project/graphviz/), and the [graphviz software, dot](https://www.graphviz.org/download/).

Dependencies:
* Table 'app_users'
* Table 'app_user_interests'
* Table 'app_interests_groups'
* Table Table 'app_interests_groups'
* path images/profile_pictures/ in Joomla.

Installation:
1. Unzip com_profile_page.
2. Move contents of the zip-folder to the Joomla install-folder.
3. Navigate to System Extensions:install in Joomla.
4. Select "Install from Folder".
5. Click "check & install".

## Navigation

