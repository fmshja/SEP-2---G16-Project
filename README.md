# Connecting Colleagues

This project focuses on the improvement of the matching algorithm and user interface design of an existing application.
It is a group project for the course 'Software Engineering Project 2' of Tampere University. 

This project consists of a several Joomla components which in combination form a Connecting Colleagues website.
There is also a pair of Python scripts which form the user matches by interacting with the database.

## Requirements
* Joomla
* Python 3.9 or higher
    * The [mariadb](https://pypi.org/project/mariadb/) python library

## The Database

The database runs on MariaDB and is provided by the server environment, such as Wampserver.
While installing Wampserver (or other such server), make sure to select MariaDB as the database.

You also need the mariadb python library for running the matching algorithm, which can be downloaded from the console with the command `pip install mariadb`.

The database structure can be viewed from `database` folder from the picture `sql_diagram.png`

Dependencies:

* MariaDB selected as the servers database

Setting up the database goes as such:
1. Start the server
2. Navigate to <http://localhost/phpmyadmin/>
3. Log in, the default username is `root` and the password is empty, if nothing else was selected during the install.
4. Open the SQL tab on the top navigation bar.
5. From the folder `database`, paste the contents of the `db_creation.sql` to the servers SQL prompt and run. The server should return an empty result set.
6. `com_interests` and `com_profile_page` wont work correctly without some interest data in the tables, so `test_data_interests.sql` has some interests for that purpose, they can be insterted from the SQL prompt if wished.
7. If user test data is needed, `test_data_users.sql` has some dummy user data that can be used.
## The components
Here are short descriptions of each Joomla component in this repository.

### Connecting home, `com_connecting_home`
This component serves as the front page for the Connecting Colleagues website.
This page greets the user and they can log in or create a new account here.
Logging in or finishing user registration should redirect user to the profile page.
Content can also be showcased on this page. 

Dependencies:
* None

Installation:
1. Unzip com_connecting_home-folder.
2. Move contents of the folder to the Joomla install-folder.
3. Navigate to System Extensions:install in Joomla.
4. Select "Install from Folder".
5. Click "check & install".

### Interest, `com_interest`
This component handles user-interest changing.
User selects three interests when they create an account.
This pages functionality is to change them and store the changes to the database.
When this component is accessed from the administration side, the admin can add new interest and interest categories for users to select.
This component should only be visible to the logged in users.

Dependencies:
* Table `'app_user_interests'`
* Table `'app_interests'`
* Table `'app_interests_groups'`

Installation:
1. Unzip com_interests.
2. Move contents of the folder to the Joomla install-folder.
3. Navigate to System Extensions:install in Joomla.
4. Select "Install from Folder".
5. Click "check & install".

### Message, `com_message`
This component houses the insite-messaging functionality.
Once the matching algorithm is run the app_formed_user_groups-table is populated with matched user groups.
User can send messages to other users who are matched in the same group as them here.
This component should only be visible to the logged in users.

Dependencies:
* Table `'app_users'`
* Table `'app_messages'`
* Table `'app_formed_user_groups'`

Installation:
1. Unzip com_message.
2. Move contents of the folder to the Joomla install-folder.
3. Navigate to System Extensions:install in Joomla.
4. Select "Install from Folder".
5. Click "check & install".

### Profile page, `com_profile_page`
This component houses the profile page functionality.
After user registration and log in, the user is redirected here.
If user is redirected here fresh out of registration, they are then required to submit some additional information here.
This information is used to initialize some other site functionalities.

Dependencies:
* Table `'app_users'`
* Table `'app_user_interests'`
* Table `'app_interests_groups'`
* Table `'app_interests_groups'`
* path `images/profile_pictures/` in Joomla.

Installation:
1. Unzip com_profile_page.
2. Move contents of the folder to the Joomla install-folder.
3. Navigate to System Extensions:install in Joomla.
4. Select "Install from Folder".
5. Click "check & install".

### Calendar, `com_calendar`
This houses the calendar functionality.
Users can use this component to mark down their planned meetings with other users. 

Dependencies:
* Table `'app_calendar_notes'`

Installation:
1. Unzip com_calendar.
2. Move contents of the folder to the Joomla install-folder.
3. Navigate to System Extensions:install in Joomla.
4. Select "Install from Folder".
5. Click "check & install".

After component installation:

Components must be set to the sites menu module in order to access the fronte end.
In order to do this:
1. Navigate to Menus:All menu items in Joomla Admin.
2. Click new.
3. Go to menu item type and select the component you want to add to the menu.
4. Give the item an appropriate title (This is the name in the menu).
5. If the component is connecting_home leave access to "public", otherwise set it to "registered".
6. Click save&close.
7. Repeat for all components.

Set the login module correctly:
1. Go to modules:login form in Joomla admin.
2. Set Login Redirection Page to profile_page-component.
3. Set Logout Redirection Page to connecting_home-component.
4. Open menu assignment tab in login form.
5. Set module assignment to only on pages selected.
6. select the connecting_home-page.

#### The matching algorithm
This component also has the Python script which performs the matching of users into groups of any size.
This is because currently this script, `run.py`, is currently invoked by the admin view of the profile page component.

There is also an optional file, `visualization.py`, which creates a simple visualization as a series of graphviz graphs and their png image renders.
This script requires both the [graphiz python library](https://pypi.org/project/graphviz/), and the [graphviz software, dot](https://www.graphviz.org/download/).

#### Configuration
When loading the admin-side view of the component for the first time (or loading after the configuration file has been removed), a configuration file `run_config.json` will be created in the component's admin folder.

Currently the settings and their defaults are:
* `python_cmd`, default value is `"python"`
    * Determines the command used to call the Python executable
* `database`
    * Determines the arguments used when connecting to the database
    * `user`, default value is `"root"`;
    * `password`, default value is `""`;
    * `host`, default value is `"127.0.0.1"`;
    * `port`, default value is `3306`;
    * `name`, default value is `"cc_database"`
        * The name of the database with the relevant tables

In most cases you can just provide the path to the `python` executable as the value of `python_cmd` setting, but if the path has spaces (like if it's under the `Program Files` folder in Windows), you need to handle it a bit differently.
On Linux you should be able to escape the space-character with a following backslash `\ `, but on Windows you must wrap the entire path in double-quotes `"`.

Also don't forget that since this is JSON, you need to use a backslash to escape every backslash and every double-quote on the command.

Example `run_config.json` on Windows with the Python folder under `C:\` and the default database settings:
```json
{
    "python_cmd": "C:\\Python39\\python.exe",
    "database": {
        "user": "root",
        "password": "",
        "host": "127.0.0.1",
        "port": 3306,
        "name": "cc_database"
    }
}
```

Example `run_config.json` on Windows with the Python folder under the `Program Files` and the default database settings:
```json
{
    "python_cmd": "\"C:\\Program Files\\Python39\\python.exe\"",
    "database": {
        "user": "root",
        "password": "",
        "host": "127.0.0.1",
        "port": 3306,
        "name": "cc_database"
    }
}
```

#### Future development of the script
Calling the script doesn't necessarily have to be done from this component, and it could be moved out of there and into another component, or made entirely into its own standalone application that's run either manually or periodically.

The script itself doesn't depend on any files used in the component.
It just needs the connecting colleagues Python library, `cc_matching.py`, to be importable, and an access to the correct database.


## Navigation on the Site
The navigation for the end user starts from the Home Page.
If the user doesn't have an account on the website yet, they are required to make one through the Log In module's Create an Account feature.
After the account creation, the user has an account with initial details (username, password, email address) set up on the server.

When the user has an account on the server, they can log in to the website.
If they happen to be logged in to the website the first time, they are required to add the following information before accessing the website:
* Their first name
* Their last name
* A short introduction of themselves
* A profile picture
* At least three (3) interests from the directory of interests

After this, the user will be redirected to the user profile page and have an access to the site's functionalities.
The users who have already logged in to the website and have had their account information finalized have an access to the site's functionalities.

The website's functionalities include the following page:
* User Profile, which displays the user account details (a full name consisting of the first and the last name, an introduction, and a profile picture)
* Messages, in which the matched user groups can send each other messages and meeting invitions
* Interests, in which more interests can picked for the user account
* Calendar, in which a user can add and remove notes regarding various meetings and other activities.

![User Navigation on the Website](https://github.com/fmshja/SEP-2---G16-Project/blob/main/graphics/User%20Navigation%20on%20the%20Website.png?raw=true)

The navigation from the admin user starts from the Joomla Administrator Login page.
After the log in, the admin user will be redirected to the Joomla Home Dashboard, whereof they can navigate to the Components menu and the following components under it:
* Interests
    * From this page, the admin user can add a new interest category, add a new interest and view the interest directory.
* Profile Page
    * From this page, the admin user can start the manual start-up for the matching algorithm

![Admin Navigation on the Website](https://github.com/fmshja/SEP-2---G16-Project/blob/main/graphics/Admin%20Navigation%20on%20the%20Website.png?raw=true)


## Setting up the Main Menu Items for the Website
Here are the instructions for setting the main menu and its menu items properly on the website.
Before explaining the process, install all the components from the repository to the (local) Joomla server.

### STEP 1
Go to the `Joomla Home Dashboard` if you are not on this page already.
After this, click the `Menus` drop-down list open and select the `Main Menu` menu item from under it.

### STEP 2
On the `Templates: Styles (Site)` page, click the `+ New` button on the upper right side of the page to add the components as menu items on the main menu.

### STEP 2.1
After clicking the `+ New` button, the view should be the same as the image below. From the `Menu Item Type` property, select a component. Set the `Title` property to be the same as the component name. Select the `Menu` property as Main Menu and the `Status` property as Published.

The `Access` property depends on the components:
* Connecting Home: `Access` -> Public
* Calendar, Interests, Messages, My Profile: `Access` -> Registered

After selecting the correct settings, click the `Save & Close` button and repeat the process to the rest of the components.

(NOTE: You can change the order of the menu items by editing the menu item's `Ordering` property afterwards.)

![The step 2.1 of setting up the main menu](https://github.com/fmshja/SEP-2---G16-Project/blob/main/graphics/instruction_images/menu_step_2.1.png?raw=true)

### STEP 3
After adding the components as menu items, unpublish the default menu item `Home` by clicking the green checkmark in front of its title.
Then set the `Connecting Home` menu item as Home by clicking the grey circle on its row.

The page should look similiar to the image below.

![The step 3 of setting up the main menu](https://github.com/fmshja/SEP-2---G16-Project/blob/main/graphics/instruction_images/menu_step_3.PNG?raw=true)

### STEP 4
The set-up for the main menu items is now complete! To make the main menu show up on the website, read the section `Setting up the Positions for the Site Module on the Website` from this README.


## Applying the Template Style for the Site
Here are the instructions for applying the default template Cassiopeia on the website.
The template is included automatically in Joomla after installing Joomla locally.

### STEP 1
Go to the `Joomla Home Dashboard` if you are not on this page already.
After this, select the `System` menu item from the menu side bar on the left.

### STEP 2
From the `System Dashboard` page, select the `Site Template Styles` item from the sub menu `Templates`.
The `Site Template Styles` item is highlighted in red on the image below.

![The step 2 of applying the template style](https://github.com/fmshja/SEP-2---G16-Project/blob/main/graphics/instruction_images/temp_step_2.png?raw=true)

### STEP 3
On the `Templates: Styles (Site)` page, you will find a list of template styles.
If the `Cassiopeia - Default` template has already been set as the default template (in other words, the icon inside the highlighted area in red in the image below is the same yellow star), you can skip the next paragraph.

Set the `Cassiopeia - Default` template (highlighted in lime green in the image below) as a default template for the website by clicking the grey circle under the `Default column`.
The circle will turn into a yellow star in a circle (highlighted in red in the image below) if the template was successfully set as the default template.

After setting the default template, click the title for the `Cassiopeia - Default` template (highlighted in lime green in the image below).

![The step 3 of applying the template style](https://github.com/fmshja/SEP-2---G16-Project/blob/main/graphics/instruction_images/temp_step_3.png?raw=true)

### STEP 4
On the `Templates: Edit Style` page, click on the `Advanced` tab. Although not strictly related to the template setup, you can change the logo on the header of the website on this tab. The process of changing the logo is descripted in the next paragraph.
If you want to only complete the template setup, skip the rest of this step and move to the step 5.

To change the logo, you need to click on the toggle `Brand` switch.
This will make the options for the logo customization to appear.
For the `Logo` property you can use any logo you want, but a default "Connecting Colleagues" logo, `cc-logo_white.png`, is provided under the `graphics`-folder in this repository.

For the `Title (alternative to logo)` property, write the alt-text for the logo.
For the default logo you can write Connecting Colleagues to the field as shown on the image below.

![The step 4 of applying the template style](https://github.com/fmshja/SEP-2---G16-Project/blob/main/graphics/instruction_images/temp_step_4.png?raw=true)

### STEP 5
Click the `Menu Assignmnet` tab on the `Templates: Edit Style` page.
Under this tab, there is a list of the menu items.
Select all the menu items except `Home` as shown in the image below. 
Click the `Save & Close` button.

![The step 5 of applying the template style](https://github.com/fmshja/SEP-2---G16-Project/blob/main/graphics/instruction_images/temp_step_5.png?raw=true)

### STEP 6
The application of the default template is now complete!


## Setting up the Positions for the Site Modules on the Website
Here are the instructions for setting up the correct positions for the site modules `Main Menu` and `Login`.
It is assumed thatyou have completed all the steps from the sections `Setting up the Main Menu for the Site` and `Applying the Template Style for the Site` before this section.

### STEP 1
Go to the `Joomla Home Dashboard` if you are not on this page already.
After this, click the `Contents` drop-down list open and select the `Site Modules` menu item from under it.

### STEP 2
On the `Modules (Site)` page, click the `+ New` button on the upper right side of the page to add the modules for the steps 3 and 4 to the website

### STEP 3: MAIN MENU
Search for the main menu module type with the search word "menu".
Add the module type by clicking the menu module. (NOTE: On Joomla 3.x.x, the main menu module can be found by searching for "main menu".)

On the `Modules: Menu` page, set the `Title` property as "Main Menu".
Click open the `Module` tab and assign the `Position` property as "search".
Then click open the `Menu Assignment` tab and assign the `Module Assignment` property as "On all pages". 

Click the `Save & Close` button.

### STEP 4: LOGIN
Search for the login module type with the search word "login".
Add the module type by clicking the login module.

On the `Modules: Menu` page, set the `Title` property as "Login", click open the `Module` tab and assign:
* the `Login Redirection Page` property as "My Profile" (the user profile),
* the `Logout Redirection Page` property as "Connecting Home" (the public home page), and
* the `Position` property as "sidebar-right".

Click open the `Menu Assignment` tab.
Assign the `Module Assignment` property as "Only on the pages selected" and assign the now appearing `Menu Selection` property as "Connecting Home" (the public home page) from the Main Menu drop-down list.

Click the `Save & Close` button.

### STEP 5
The set-up of the site module positions is now complete!
