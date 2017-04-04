#PHP server backend


### What it is

This is a lightweight server-backend which can be used **instead** of the
Java-appengine-server-backend.
It is meant to be easy to install without needing much requirements or ressources.
The scripts asume, that the server is accessed only by people who trust each other.
So every admin-account has access to all experiments on the server.

Its main purpose is for researchers who want to run experiments on their own server.
but dont have the means to run a full blown appengine-server.

Data are stored in CSV and can be downloaded (and generated) with relativly
low performance drawbacks.
Data are not meant to be read by the web-frontend for reviewing the data.
So if you want to look into your gathered data you have to download them first.



### What does not work

* Experiment-Invitations
* Different experiment lists (_new_, _popular_, mine_, _joined_ and _admin_
  are all identical)
* Web-data-preview
* Export to JSON or HTML
* Specific experiment administrators (everyone who has a log in to the server, can
  change all experiments)
* End to end-encryption
* Media (foto, or recordings) - never tested or implemented



### Requirements

__PHP__
	* For running the backend-server.
	
__Apache-server__
	* To read the additional-Paco-headers, `apache_request_headers()` is used.
	
__.htaccess__
	* To limit the access from outside to the directories _data/_ and _include/_.
	* Module mode_rewrite: So the php scripts can be called without their
	  _.php_-extention (which is what the paco-app does).

__Access to shell-commands (recommended)__
	* Data is stored in two files. In one is the actual data, in the other
	  are the variable-names for the data.
	  If you generate a CSV-file, these two files are just merged together
	  Unfortunately, php has no way to mere file without reading them first.
	  This is why the export uses the sytsem command `cat` (or `type` on windows)
	  which is way faster.
	  If this command is not available, the files are merged via php - then
	  you may want to increase the max excecution time of php-cripts on your
	  server or you may end up getting a timeout if you have very big data-files.

	  You can also access the data directly over the filesystem.
	  Data is stored in _data/events/inputs/ID_ and the keys are stored
	  in _data/events/keys/ID_.



### How to install

1) Copy all the files and directories to your server root.
2) Additionally copy the web folder from _Paco-Server/ear/default/_
   to your server root.

You should have the following server-structure in your root:
```
data/
include/
web/
change_login.php
events.php
experiments.php
jobStatus.php
jobStatus.php
.htaccess
```

All files that are subject to change are stored in _data/_
So, if you want to backup your server-data you only need to save the
_data/_-directory.


###### Use a subfolder (not root)

For the web-frontend to work properly, the request-uri has to be root. But
you can put all the files into a subfolder and make an internal redirect.
This way all files are still requested as if they were stored in your server-root
but the server actually presents files, that are stored elsewhere (so you cant
access the web-frontend with an url like _mydomain.com/**subfolder**_, but you
can have your files in _SERVERROOT/path/to/subdirectoy/_ and route them to
_mydomain.com/_)
The easiest way to do such an redirect is by putting a _.htaccess_-file into
root with the following content:

```
RewriteEngine On
RewriteCond %{REQUEST_URI} !^/PATH/TO/SUBFOLDER/
RewriteRule ^(.*)$ /PATH/TO/SUBFOLDER/$1 [L]
```
(replace _PATH/TO/SUBFOLDER_ with the path to you actual subfolder)

Note that this will redirect **all** calls into your _PATH/TO/SUBFOLDER_.
So other projects might not work until you delete or rename the
 _.htaccess_-file.


### Admin-Accounts

The login-system of the php-server does not use a google account for logins,
so you have to set up at least one admin account before you can start.
Also keep in mind, that it doesnt matter which account is used for logging in.
Everyone who is logged in, can view and change all experiments that are saved
on the server.
FOr security reasons admin accounts can only be set if you call the scripts
from the machine itself (localhost).
If you need to set up admin accounts on a webserver, you have two options:

__Option 1:__ Create and copy the admin accounts from your local server:
	* Set up a local server on your machine,
	* You need at least the following file structure for the _change_login.php_ to work:
		```
		change_login.php
		inlucde/hash_fu.php
		data/
		```

	* Call the script _change_login.php_ and set up your admin-accounts.
	* Copy the newly created file _data/logins_ to the web-server.

__Option 2:__ Temporarily change `check_local()`
	* Change the function `check_local()` in _include/hash_fu.php_ (line 2)
	  into something like:
		```php
		function check_local() {
			return true;
		}
		```
	* Set up your admin-accounts.
	* __Dont forget to revert this change when you are done or anyone will
	  be able to set up an admin account and change/delete your exeriments!__



### Data structure

__Experiments__
	* Experiments are saved (in JSON-format) to _data/experiments/published/_,
	  _data/experiments/unpublished/_ and _data/experiments/restricted/_
	* to speed up data-organisation, index files are saved to _data/experiment_index/_
	  All files are merged into _data/experiment_index/merged_ which is used
	  by _events.php_.
	* Also there is an _index_-file in _data/experiments/restricted/_ for
	  experiments that need a key to be accessed.

__Events__
	* Userdata are only saved in CSV. JSON and HTML are not available.
	* When creating a new experiment a key file with the variable-names is
	  saved into _data/events/keys/_.
	* Userdata are saved to _data/events/inputs/_.
	* Generated CSV-files are stored into _data/events/datafiles/_
	  These files are not deleted automatically. If you want to remove them
	  you have to do that in the datastructure.
	  

The files in _data/experiment_index/_ and _data/events/keys/_ are only used
when userdata is stored. The experiment-editor works fine without them.
So if some of these files ever get messed up, just save the concerning experiment
over the web-frontend and new files (including _data/experiment_index/merged_
and _data/experiments/restricted/index_) should be created.