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
$from = null;
foreach ($argv as $arg)
{
	if (preg_match('/--path=(.*)/', $arg, $match)) {
		$path = $match[1];
	} else if (preg_match('/--email=(.*)/', $arg, $match)) {
		$email = $match[1];
	} else if (preg_match('/--subject=(.*)/', $arg, $match)) {
		$subject = $match[1];
	} else if (preg_match('/--from=(.*)/', $arg, $match)) {
		$from = $match[1];
	}
}

if (is_null($path) || is_null($email) || is_null($from))
{
	echo("Usage:\n");
	echo("  " . basename(__FILE__) . " --path=/path/to/repo --email=list@example.com --from=my@email.com\n");
	exit(1);
}

if (!chdir($path)) {
	echo("Path does not exist: " . $path . "\n");
	exit(2);
}

// request repo-URL from git remote
$remote = `git remote -v`;
$repo = 'unknown';
if (preg_match('/origin\s*(\S*)\s*\(fetch\)\n/', $remote, $match))
{
    $repo = $match[1];
}

$pullResult = `git pull 2>&1`;
if (preg_match('/^\S* ([a-z0-9]{7}\.\.[a-z0-9]{7})\n/', $pullResult, $match))
{
	$revs = $match[1];
	$wikiDiffUrl = str_replace('.wiki.git', '/wiki', $repo) . '/_compare/' . $revs;
	$changeLog = `git log --pretty=format:'%h - %s (%cr) <%an>' $revs`;
	
	if (is_null($subject)) {
		$subject = '[SCM]: ' . $repo . ' was updated';
	}
	$body = "To see the changes, visit:\n" . $wikiDiffUrl . "\n\nChangelog:\n" . $changeLog . "\n";
	mail($email, $subject, $body, "From: $from");
}
// else no updates

