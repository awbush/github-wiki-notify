#!/usr/bin/env php
<?php
/**
 * Poll updates to a Github wiki and send notifications about updates.
 * 
 * Usage is simple, just cron it:
 * 
 *     github-wiki-notify.php --path=/path/to/repo --email=list@example.com --subject="Wiki updated!"
 * 
 * Optional parameter:
 *     --from=sender@example.com	sender email address, otherwise the --email address is used
 *     --verbose=LEVEL				LEVEL = 0..5, see class Level, 0 = quiet .. 5 = debugging, default = 0
 *
 * @author Anthony Bush
 * @version 1.0.1
 * @copyright Anthony Bush, 20 December, 2011
 * @license <http://www.opensource.org/licenses/bsd-license.php>
 * @package default
 **/

/**
 * verbose level
 **/
class Level {
	const NOTHING = 0;
	const ALERT   = 1;
	const ERROR   = 2;
	const WARNING = 3;
	const INFO    = 4;
	const DEBUG   = 5;
}

/**
 * Define DocBlock
 **/

$path = null;
$email = null;
$from = null;
$subject = null;
$verbose = Level::NOTHING;
foreach ($argv as $arg)
{
	if (preg_match('/--path=(.*)/', $arg, $match)) {
		$path = $match[1];
	} else if (preg_match('/--email=(.*)/', $arg, $match)) {
		$email = $match[1];
	} else if (preg_match('/--from=(.*)/', $arg, $match)) {
		$from = $match[1];
	} else if (preg_match('/--subject=(.*)/', $arg, $match)) {
		$subject = $match[1];
	} else if (preg_match('/--verbose=(.*)/', $arg, $match)) {
		$verbose = $match[1];
	}
}

if (is_null($path) || is_null($email))
{
	echo("Usage:\n");
	echo("  " . basename(__FILE__) . " --path=/path/to/repo --email=list@example.com [--from=sender@example.com] [--verbose=(0..5)]\n");
	exit(1);
}

if (is_null($from))
{
	$from = $email;
}

if (!chdir($path)) {
	echo("Path does not exist: " . $path . "\n");
	exit(2);
}

// request repo-URL with `git remote`
// which is for HTTPS:	https://github.com/OWNER/REPO.wiki.git
// and for SSH:			git@github.com:OWNER/REPO.wiki.git
$remote = `git remote get-url origin`;
$repo = 'unknown';
if (preg_match('/github.com[:\/](\S*)\.git$/', $remote, $match)) {
	$repo = $match[1];
	verbose( "remote url found: $repo", Level::INFO);
}
else {
	verbose( "No remote url found!", Level::ERROR);
	exit(3);
}

// pull possible changes and analyze result
// be aware that git output is localized!
$pullResult = `git pull -v 2>&1`;
if (preg_match('/^\S* ([a-z0-9]{7}\.\.[a-z0-9]{7})\n/m', $pullResult, $match)) {
	$revs = $match[1];
	verbose( "updated: $revs", Level::INFO);
	$wikiDiffUrl = 'https://github.com/' . str_replace('.wiki', '/wiki', $repo) . '/_compare/' . $revs;
	$changeLog = `git log --pretty=format:'%h - %s (%cr) <%an>' $revs`;
	
	if (is_null($subject)) {
		$subject = '[SCM]: ' . $repo . ' was updated';
	}
	$body = "To see the changes, visit:\n" . $wikiDiffUrl . "\n\nChangelog:\n" . $changeLog . "\n";
	mail($email, $subject, $body, "From: $from");
}
else {
	// this may be because the repo is up to date, what is okay
	verbose( "No match in pullResult!", Level::INFO);
	// but can also be an issue with the regex, because of localization or git changes
	verbose( "\npullResult:");
	verbose( $pullResult);
}

function verbose($message, $level = Level::DEBUG)
{
	global $verbose;

	if ($verbose >= $level) {
		echo $message . "\n";
	}
}
