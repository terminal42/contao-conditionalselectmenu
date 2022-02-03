<?php

declare(strict_types=1);

namespace Terminal42\ConditionalSelectMenuBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

/**
 * @Callback(table="tl_form_field", target="fields.conditionField.options")
 */
class ConditionFieldOptionsListener
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(DataContainer $dc): array
    {
        $options = [];

        $fields = $this->connection->fetchAllAssociative(
            'SELECT id, name FROM tl_form_field WHERE pid=(SELECT pid FROM tl_form_field WHERE id=?) AND id!=? AND (type=? OR type=?)',
            [$dc->id, $dc->id, 'select', 'conditionalselect']
        );

        foreach ($fields as $field) {
            $options[$field['id']] = $field['name'];
        }

        return $options;
    }
}
