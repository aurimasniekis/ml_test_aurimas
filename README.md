Test Application based on Symfony 2.6.1 version
========================

1) Installation
----------------------------------

After (cloning / downloading) project you must execute these steps to start testing project

### Steps

#### 1) Install vagrant machine
You need to have Vagrant and VirtualBox installed to be able test this application. If you have already installed please continue.

	vagrant up

After installation complete, log into box and navigate to website folder.

	vagrant ssh
	cd /var/www/

#### 2) Install vendor dependencies

You need to install all the necessary dependencies. Run the following command:

    composer install
    
#### 4) Prepare database

You need to create a database first:

    app/console doctrine:database:create

Then you need to load database schema:

	app/console doctrine:schema:update --force

#### 4) Test application

Now you can try access application via url:

	http://ml_test_aurimas.dev


2) To-Do list
----------------------------------
Here is the list of functionality which is not implemented.

* Label, Milestone, Assignee control via application (Now just showing)
* Implement all events of issue currently only basic ones are showing
* Implement comment deletion
* Implement other owner repositories listing in home page
* Figure out better way to load statistics for repository listing (Takes aroung 500ms to run all queries to github to receive issue counts)
* Rewrite CachedHttpClient to take more advantage of Redis comamand pipelining.

All libraries and bundles included in the Symfony Standard Edition are
released under the MIT or BSD license.

Enjoy!