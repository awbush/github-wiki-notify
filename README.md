What
----

This is a simple tool that polls for updates to a Github Wiki and sends an email with a link to the changes and a brief changelog.

Installation
------------

Requirements:

- PHP
- Email (your server needs to be able to send email)

Get a checkout of your Github repo:

	cd /some/path/
	git clone git@github.com:org/repo.wiki.git
	
Then create a cron (e.g. `crontab -e`):

	*/15 * * * * github-wiki-notify.php --path="/some/path/repo.wiki" --email="list@example.com"

Problems? Want to contribute?
-----------------------------

[Report issues](https://github.com/awbush/github-wiki-notify/issues) on github.  Use [pull requests](http://help.github.com/send-pull-requests/) to contribute code.  Versioning will be done as defined by [Semantic Versioning](http://semver.org/).
