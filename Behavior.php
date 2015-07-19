<?php
/**
 * Created for yii2-multi-input
 * in extweb.org with love!
 * Artem Dekhtyar mail@artemd.ru
 * 17.07.2015
 */

namespace tigokr\multinput;

use Codeception\Util\Debug;
use yii\base\ErrorException;
use yii\db\ActiveRecord;

class Behavior extends \yii\base\Behavior
{

    public $relations;

    protected $_order = 'ord';

    protected $_values;

    public function events()
    {
        return [
//            ActiveRecord::EVENT_AFTER_FIND => 'loadRelationsIntoLists',

            ActiveRecord::EVENT_AFTER_INSERT => 'saveRelations',
            ActiveRecord::EVENT_AFTER_UPDATE => 'saveRelations',

            ActiveRecord::EVENT_AFTER_DELETE => 'deleteRelations',
        ];
    }

//    public function loadRelationsIntoLists(){
//        /**
//         * @var $primaryModel \yii\db\ActiveRecord
//         */
//        $primaryModel = $this->owner;
//
//        foreach ($this->relations as $list_name => $relation_config) {
//            $relation_name = $relation_config['relation'];
//
//            $relation = $primaryModel->getRelation($relation_name);
//            $relation_data = $relation->asArray()->all();
//
//            $primaryModel->$list_name = $relation_data;
//        }
//    }

    public function saveRelations()
    {
        /**
         * @var $primaryModel \yii\db\ActiveRecord
         */
        $primaryModel = $this->owner;

        if (is_array($primaryModelPk = $primaryModel->getPrimaryKey())) {
            throw new ErrorException("This behavior does not support composite primary keys");
        }

        foreach ($this->relations as $list_name => $relation_config) {
            $relation_name = $relation_config['relation'];
            /** @var \yii\db\ActiveQuery $relation */
            $relation = $primaryModel->getRelation($relation_name);
            /** @var \yii\db\ActiveRecord $foreignModel */
            $foreignModel = new $relation->modelClass();

            // many-to-many relation save
            if ($relation->multiple && !empty($relation->via)) {
                $this->saveManyManyList($foreignModel, $relation_config, $primaryModel->$list_name);

                // one-to-many relation save
            } elseif ($relation->multiple && !empty($relation->link)) {
                $this->saveOneManyList($foreignModel, $relation_config, $primaryModel->$list_name);

            } else {
                throw new ErrorException('Relationship type not supported.');
            }

        }

    }

    public function deleteRelations()
    {
        /**
         * @var $primaryModel \yii\db\ActiveRecord
         */
        $primaryModel = $this->owner;

        if (is_array($primaryModelPk = $primaryModel->getPrimaryKey())) {
            throw new ErrorException("This behavior does not support composite primary keys");
        }

        foreach ($this->relations as $list_name => $relation_config) {
            $relation_name = $relation_config['relation'];

            $delete = false;
            if (isset($relation_config['delete_related_after_delete']) && $relation_config['delete_related_after_delete'])
                $delete = true;

            $primaryModel->unlinkAll($relation_name, $delete);
        }
    }


    /**
     * @param $foreignModel \yii\db\ActiveRecord
     * @param $relation_config []
     * @param $related_list []
     */
    private function saveOneManyList($foreignModel, $relation_config, $related_list)
    {
        if (!is_array($related_list) && empty($related_list))
            return;

        /**
         * @var $primaryModel \yii\db\ActiveRecord
         */
        $primaryModel = $this->owner;
        $link = $primaryModel->getRelation($relation_config['relation'])->link;
        list($manyTableFkColumn) = array_keys($link);

        $relation_name = $relation_config['relation'];

        if (count($foreignModel->primaryKey()) > 1)
            throw new ErrorException("This behavior does not support composite primary keys");

        list($manyTablePkColumn) = $foreignModel->primaryKey();

        $old_related_records = \yii\helpers\ArrayHelper::map($primaryModel->$relation_name, $manyTablePkColumn, $manyTablePkColumn);
        $new_related_records = array_filter(\yii\helpers\ArrayHelper::map((array)$related_list, $manyTablePkColumn, $manyTablePkColumn));

        $del_related_records = array_diff($old_related_records, $new_related_records);

        $delete = false;
        if (isset($relation_config['delete_related_after_update']) && $relation_config['delete_related_after_update'])
            $delete = true;

        // Remove old relations
        foreach ($del_related_records as $foreignModel_id) {
            $primaryModel->unlink($relation_name, $foreignModel::findOne($foreignModel_id), $delete);
        }

        if (!empty($related_list) && is_array($related_list)) {
            foreach ($related_list as $ord => $related_data) {
                // check old related models
                if (isset($related_data[$manyTablePkColumn])) {
                    // if exists then update
                    $related_model = $foreignModel::findOne($related_data[$manyTablePkColumn]);
                } else {
                    $className = $foreignModel::className();
                    $related_model = new $className;
                }

                /** @var \yii\db\ActiveRecord $related_model */
                $related_model->setAttributes($related_data);
                $related_model->$manyTableFkColumn = $primaryModel->primaryKey;

                // if related model consist order rule (field)
                if (isset($relation_config['order']) && $related_model->hasAttribute($relation_config['order']))
                    $related_model->{$relation_config['order']} = $ord;

                if ($related_model->validate()) {
                    $primaryModel->link($relation_name, $related_model);
                } else {
                    foreach ($related_model->errors as $error) {
                        $primaryModel->addError($relation_name, $error);
                    }
                }

            }
        }
    }

    /**
     * @param $foreignModel \yii\db\ActiveRecord
     * @param $relation_config []
     * @param $related_list []
     */
    private function saveManyManyList($foreignModel, $relation_config, $related_list)
    {
        if (!is_array($related_list) && empty($related_list))
            return;

        /**
         * @var $primaryModel \yii\db\ActiveRecord
         */
        $primaryModel = $this->owner;
        $relation_name = $relation_config['relation'];
        $relation = $primaryModel->getRelation($relation_name);
        $link = $relation->link;
        /**
         * @var $foreignModel \yii\db\ActiveRecord
         */
        $foreignModel = new $relation->modelClass();

        list($manyTablePkColumn) = $foreignModel->primaryKey();

        $old_related_records = \yii\helpers\ArrayHelper::map($primaryModel->$relation_name, $manyTablePkColumn, $manyTablePkColumn);
        $new_related_records = array_filter(\yii\helpers\ArrayHelper::map((array)$related_list, $manyTablePkColumn, $manyTablePkColumn));

        $del_related_records = array_diff($old_related_records, $new_related_records);

        $delete = false;
        if (isset($relation_config['delete_related_after_update']) && $relation_config['delete_related_after_update'])
            $delete = true;

        // Remove old relations
        foreach ($del_related_records as $foreignModel_id) {
            $primaryModel->unlink($relation_name, $foreignModel::findOne($foreignModel_id), $delete);
        }


        if (!empty($related_list) && is_array($related_list)) {
            foreach ($related_list as $ord => $related_data) {
                // check old related models
                if (isset($related_data[$manyTablePkColumn])) {
                    // if exists then update
                    $related_model = $foreignModel::findOne($related_data[$manyTablePkColumn]);
                } else {
                    $className = $foreignModel::className();
                    $related_model = new $className;
                }

                /** @var \yii\db\ActiveRecord $related_model */
                $related_model->setAttributes($related_data);

                if ($related_model->save()) {
                    $cols = [];
                    // if related model consist order rule (field)
                    if (isset($relation_config['order']))
                        $cols[$relation_config['order']] = $ord;

                    $primaryModel->link($relation_name, $related_model, $cols);
                } else {
                    foreach ($related_model->errors as $error) {
                        $primaryModel->addError($relation_name, $error);
                    }
                }

            }
        }

    }

}