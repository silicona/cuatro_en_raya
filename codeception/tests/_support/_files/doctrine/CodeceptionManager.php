<?php

//namespace Support\Doctrine;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once "../vendor/autoload.php";

class CodeceptionManager
{

  public static function createEntityManager()
  {
    // Create a simple "default" Doctrine ORM configuration for Annotations
    $isDevMode = true;
    $proxyDir = null;
    $cache = null;
    $useSimpleAnnotationReader = false;
    $config = Setup::createAnnotationMetadataConfiguration(array(__DIR__ . "/entities"), $isDevMode, $proxyDir, $cache, $useSimpleAnnotationReader);
    // or if you prefer XML
    // $config = Setup::createXMLMetadataConfiguration(array(__DIR__."/config/xml"), $isDevMode);

    // database configuration parameters
    $conn = array(
      'driver' => 'pdo_sqlite',
      'path' => __DIR__ . '/db.sqlite',
    );

    // obtaining the entity manager
    return EntityManager::create($conn, $config);
  }
}
