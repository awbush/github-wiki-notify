#!/usr/bin/env php
<?php
/**
 * Poll updates to a Github wiki and send notifications about updates.
 * 
 * Usage is simple, just cron it:
 * 
 *     github-wiki-notify.php --path=/path/to/repo --email=list@example.com --subject="Wiki updated!"
 *
 * @author Anthony Bush
 * @version 1.0.1
 * @copyright Anthony Bush, 20 December, 2011
 * @license <http://www.opensource.org/licenses/bsd-license.php>
 * @package default
 **/

/**
 * Define DocBlock
 **/

$path = null;
$email = null;
$subject = null;
foreach ($argv as $arg)
{
	if (preg_match('/--path=(.*)/', $arg, $match)) {
		$path = $match[1];
	} else if (preg_match('/--email=(.*)/', $arg, $match)) {
		$email = $match[1];
	} else if (preg_match('/--subject=(.*)/', $arg, $match)) {
		$subject = $match[1];
	}
}

if (is_null($path) || is_null($email))
{
	echo("Usage:\n");
	echo("  " . basename(__FILE__) . " --path=/path/to/repo --email=list@example.com\n");
	exit(1);
}

if (!chdir($path)) {
	echo("Path does not exist: " . $path . "\n");
	exit(2);
}

$pullResult = `git pull 2>&1`;
if (preg_match('/From github\.com:(.*)\n\s*([^\s]+)/', $pullResult, $match))
{
	$repo = $match[1];
	$revs = $match[2];
	$wikiDiffUrl = 'https://github.com/' . str_replace('.wiki', '/wiki', $repo) . '/_compare/' . $revs;
	$changeLog = `git log --pretty=format:'%h - %s (%cr) <%an>' $revs`;
	
	if (is_null($subject)) {
		$subject = '[SCM]: ' . $repo . ' was updated';
	}
	$body = "To see the changes, visit:\n" . $wikiDiffUrl . "\n\nChangelog:\n" . $changeLog . "\n";
	mail($email, $subject, $body, "From: $email");
}
// else no updates
