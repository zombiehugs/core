<?php

define("DEBUG", true);

$CONFIG = array(
"installed" => false,
"dbtype" => "sqlite",
"dbname" => "owncloud",
"dbuser" => "",
"dbpassword" => "",
"dbhost" => "",
"dbtableprefix" => "",

/* Define the salt used to hash the user passwords. All your user passwords are lost if you lose this string. */
"passwordsalt" => "",

/* Force use of HTTPS connection (true = use HTTPS) */
"forcessl" => false,
"enablebackup" => false,
"theme" => "",
"3rdpartyroot" => "",
"3rdpartyurl" => "",
"defaultapp" => "files",
"knowledgebaseenabled" => true,
"knowledgebaseurl" => "",
"appstoreenabled" => true,
"appstoreurl" => "",
"mail_smtpmode" => "sendmail",
"mail_smtphost" => "127.0.0.1",
"mail_smtpauth" => "false",
"mail_smtpname" => "",
"mail_smtppassword" => "",
"appcodechecker" => "",

/* Check if ownCloud is up to date */
"updatechecker" => true,

/* Place to log to, can be owncloud and syslog (owncloud is log menu item in admin menu) */
"log_type" => "owncloud",

/* File for the owncloud logger to log to, (default is ownloud.log in the data dir */
"logfile" => "",
"loglevel" => "",
// "datadirectory" => ""
);
?>
