#
# Table structure for table 'tx_typo3profiler_sql'
#
CREATE TABLE tx_typo3profiler_sql (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    type tinytext,
    query text,
    time float DEFAULT '0' NOT NULL,
    page int(11) DEFAULT '0' NOT NULL,
    typo3mode tinytext,
    backtrace text,

    PRIMARY KEY (uid),
    KEY parent (pid)
);

#
# Table structure for table 'tx_typo3profiler_page'
#
CREATE TABLE tx_typo3profiler_page (
    uid int(11) NOT NULL auto_increment,
    pid int(11) DEFAULT '0' NOT NULL,
    tstamp int(11) DEFAULT '0' NOT NULL,
    crdate int(11) DEFAULT '0' NOT NULL,
    cruser_id int(11) DEFAULT '0' NOT NULL,
    deleted tinyint(4) DEFAULT '0' NOT NULL,
    hidden tinyint(4) DEFAULT '0' NOT NULL,
    parsetime int(11) DEFAULT '0' NOT NULL,
    page int(11) DEFAULT '0' NOT NULL,
    logts longblob,
    size tinytext,
    nocache tinytext,
    userint tinytext,
    nbqueries tinytext,

    PRIMARY KEY (uid),
    KEY parent (pid)
);