<?php
class sfSyncSignableNamesTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', 'backend'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('force', null, sfCommandOption::PARAMETER_REQUIRED, 'force: update', false),
      new sfCommandOption('models', null, sfCommandOption::PARAMETER_REQUIRED, 'models: [Order,OrderItem]', false),
    ));

    $this->namespace = 'signable';
    $this->name = 'sync';
    $this->briefDescription = 'Sync sfSyncSignableNamesTask Data';
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $this->databaseManager = new sfDatabaseManager($this->configuration);
    sfContext::createInstance($this->configuration);
    
    $connection = $this->databaseManager->getDatabase('master')->getConnection();
    
    $manager = Doctrine_Manager::getInstance();
    $manager->setAttribute(Doctrine_Core::ATTR_VALIDATE, Doctrine_Core::VALIDATE_NONE);
    
    $this->options = $options;
    $this->logSection('import', "******** Start ".date('Y-m-d H:i:s')." ********");

    $model_names = explode(',', $options['models']);
    foreach ($model_names as $model_name){
      $model_name = trim($model_name);
      if (class_exists($model_name)){
        $table = Doctrine_Core::getTable($model_name);

        $this->logSection('check', "Check Table $model_name");

        $q = $table->createQuery('o');
        $q->leftJoin('o.Creator c');
        $q->leftJoin('o.Updator u');
        $q->orderBy('o.id DESC');
        $items = $q->execute(array(), Doctrine_Core::HYDRATE_ON_DEMAND);

        foreach ($items as $item){
          if ($item->getCreatedBy() && $user = $item->getCreator()){
            $item->set('created_by_name', $user->getName());
            $created_by_name = $user->getName();
          } else {
            $created_by_name = '';
          }

          if ($item->getUpdatedBy() && $user = $item->getUpdator()){
            $item->set('updated_by_name', $user->getName());
            $updated_by_name = $user->getName();
          } else {
            $updated_by_name = '';
          }

          $modified = $item->getModified();
          if (isset($modified['created_by_name']) OR isset($modified['updated_by_name'])){
            $item->save();
            $this->logSection($model_name, " - Updated item: $item -> C: $created_by_name / U: $updated_by_name");
          }
        }

      }
    }

    
    $this->logSection('import', "******** End ".date('Y-m-d H:i:s')." ********");
    
  }
  

}