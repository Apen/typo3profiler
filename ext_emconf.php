<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "typo3profiler".
 *
 * Auto generated 10-02-2016 11:33
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'TYPO3 profiler',
  'description' => 'TYPO3 profiler store the slowest queries and parsetimes of page generation. It can help you to optimize your website performance.',
  'category' => 'module',
  'version' => '1.3.5',
  'state' => 'stable',
  'uploadfolder' => false,
  'createDirs' => '',
  'clearcacheonload' => true,
  'author' => 'CERDAN Yohann [Site-nGo]',
  'author_email' => 'cerdanyohann@yahoo.fr',
  'author_company' => '',
  'constraints' => 
  array (
    'depends' => 
    array (
      'php' => '5.3.7-7.0.99',
      'typo3' => '6.2.0-7.6.99',
    ),
    'conflicts' => 
    array (
    ),
    'suggests' => 
    array (
    ),
  ),
);

