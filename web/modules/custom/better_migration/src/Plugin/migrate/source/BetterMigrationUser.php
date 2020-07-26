<?php
/**
 * @file
 * Contains \Drupal\better_migration\Plugin\migrate\source\BetterMigrationUser.
 */

namespace Drupal\better_migration\Plugin\migrate\source;

use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Extract users from Drupal 7 database.
 *
 * @MigrateSource(
 *   id = "better_migration_user"
 * )
 */
class BetterMigrationUser extends SqlBase
{

  /**
   * {@inheritdoc}
   */
    public function query()
    {
        return $this->select('users', 'u')
      ->fields('u', array_keys($this->baseFields()))
      ->condition('uid', 0, '>');
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = $this->baseFields();
        $fields['first_name'] = $this->t('First Name');
        $fields['last_name'] = $this->t('Last Name');
        $fields['organisation'] = $this->t('Organisation');
        $fields['roles'] = $this->t('Roles');

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareRow(Row $row)
    {
        $uid = $row->getSourceProperty('uid');

        // Organisation
        $result = $this->getDatabase()->query('
      SELECT
        fld.field_first_name_value
      FROM
        {field_data_field_first_name} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
        foreach ($result as $record) {
            $row->setSourceProperty('first_name', $record->field_first_name_value);
        }


        // Organisation
        $result = $this->getDatabase()->query('
      SELECT
        fld.field_last_name_value
      FROM
        {field_data_field_last_name} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
        foreach ($result as $record) {
            $row->setSourceProperty('last_name', $record->field_last_name_value);
        }

        // Organisation
        $result = $this->getDatabase()->query('
      SELECT
        fld.field_organisation_value
      FROM
        {field_data_field_organisation} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
        foreach ($result as $record) {
            $row->setSourceProperty('organisation', $record->field_organisation_value);
        }

        // Roles
        $roles = [];
        $result = $this->getDatabase()->query('
         SELECT
           sr.rid
         FROM
           {users_roles} sr
         WHERE
           sr.uid = :uid
       ', array(':uid' => $uid));
        foreach ($result as $record) {
            $roles[] = $record->rid;
        }

        if (!empty($roles)) {
            $row->setSourceProperty('roles', $roles);
        }

        return parent::prepareRow($row);
    }

    /**
     * {@inheritdoc}
     */
    public function getIds()
    {
        return array(
      'uid' => array(
        'type' => 'integer',
        'alias' => 'u',
      ),
    );
    }

    /**
     * Returns the user base fields to be migrated.
     *
     * @return array
     *   Associative array having field name as key and description as value.
     */
    protected function baseFields()
    {
        $fields = array(
      'uid' => $this->t('User ID'),
      'name' => $this->t('Username'),
      'pass' => $this->t('Password'),
      'mail' => $this->t('Email address'),
      'signature' => $this->t('Signature'),
      'signature_format' => $this->t('Signature format'),
      'created' => $this->t('Registered timestamp'),
      'access' => $this->t('Last access timestamp'),
      'login' => $this->t('Last login timestamp'),
      'status' => $this->t('Status'),
      'timezone' => $this->t('Timezone'),
      'language' => $this->t('Language'),
      'picture' => $this->t('Picture'),
      'init' => $this->t('Init'),
    );
        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function bundleMigrationRequired()
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function entityTypeId()
    {
        return 'user';
    }
}
