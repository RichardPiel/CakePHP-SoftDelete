<?php
declare(strict_types=1);

namespace SoftDelete\Model\Behavior;

use Cake\ORM\Behavior;
use Cake\ORM\Query;
use Cake\ORM\Table;

/**
 * SoftDelete behavior
 */
class SoftDeleteBehavior extends Behavior
{
    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [
        'fields' => [
            'is_active' => 'is_active',
            'deleted' => 'archived'
        ],
        'implementedFinders' => [
            'Active' => 'findActive',
            'NotArchived' => 'findNotArchived',
            'ActiveAndNotArchived' => 'findActiveAndNotArchived'
        ],
        'implementedMethods' => [
            'softDelete' => 'softDelete'
        ],
        'null' => [],
        'anonymize' => []
    ];

    /**
     * Softdelete d'une entité
     * - RAZ des champs
     * - anonymisation
     *
     * @param \Cake\Datasource\EntityInterface $entity
     * @return boolean
     */
    public function softDelete(\Cake\Datasource\EntityInterface $entity): bool
    {

        if (sizeof($this->_config['null']) > 0) {
            foreach ($this->_config['null'] as $key => $null) {
                $entity->{$null} = NULL;
            }
        }
        if (sizeof($this->_config['anonymize']) > 0) {
            foreach ($this->_config['anonymize'] as $key => $anonymize) {
                $entity->{$anonymize} = bin2hex(random_bytes(20));
            }
        }

        $entity->{$this->_config['fields']['deleted']} = new \DateTime();

        if ($this->_table->save($entity)) {
            return true;
        }
        return false;
    }
    
    /**
     * Finder activées
     *
     * @param Query $query
     * @param array $options
     * @return Query $query
     */
    public function findActive(Query $query, array $options): Query
    {
        return $query
            ->where([
                $this->_table->getAlias() . '.' . $this->_config['fields']['is_active'] => 1
            ]);
    }

    /**
     * Finder non supprimées
     *
     * @param Query $query
     * @param array $options
     * @return Query $query
     */
    public function findNotArchived(Query $query, array $options): Query
    {

        return $query
            ->where([
                $this->_table->getAlias() . '.' . $this->_config['fields']['deleted'] . ' IS NULL'
            ]);
    }

    /**
     * Finder activées et non supprimées
     *
     * @param Query $query
     * @param array $options
     * @return Query $query
     */
    public function findActiveAndNotArchived(Query $query, array $options): Query
    {
        return $query
            ->find('active')
            ->find('notArchived');
    }
}
