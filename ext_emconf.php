<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "solradmin".
 *
 * Auto generated 21-02-2017 10:38
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
  'title' => 'Solr Administration',
  'description' => 'With this module you can directly access your solr module, search records with an interface, delete some specifics records and do other operations (with multi core support)',
  'category' => 'module',
  'version' => '1.2.0',
  'state' => 'stable',
  'uploadfolder' => true,
  'createDirs' => '',
  'clearcacheonload' => true,
  'author' => 'CERDAN Yohann',
  'author_email' => 'cerdanyohann@yahoo.fr',
  'author_company' => '',
  'constraints' =>
  array (
    'depends' =>
    array (
      'typo3' => '7.6.0-7.6.99',
    ),
    'conflicts' =>
    array (
    ),
    'suggests' =>
    array (
    ),
  ),
);
