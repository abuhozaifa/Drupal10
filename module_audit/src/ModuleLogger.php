<?php
namespace Drupal\module_audit;
use Drupal\Core\Database\Connection;use Drupal\Core\Session\AccountProxyInterface;use Drupal\Core\Extension\ModuleHandlerInterface;use Symfony\Component\HttpFoundation\RequestStack;
class ModuleLogger{function __construct(protected Connection $database,protected AccountProxyInterface $currentUser,protected ModuleHandlerInterface $moduleHandler,protected RequestStack $requestStack){}
public function save($modules,$action){$source=(PHP_SAPI==='cli')?'Drush':'UI';foreach($modules as $m){$this->database->insert('module_audit_log')->fields(['module_name'=>$m,'action'=>$action,'username'=>$this->currentUser->getAccountName(),'source'=>$source,'enabled_modules_count'=>count($this->moduleHandler->getModuleList()),'created'=>time()])->execute();}}}