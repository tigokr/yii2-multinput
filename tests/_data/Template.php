<?php
/**
 * Created for yii2-multi-input
 * in extweb.org with love!
 * Artem Dekhtyar mail@artemd.ru
 * 17.07.2015
 */

namespace data;

class Template extends \yii\db\ActiveRecord
{

    /*
     * relation stubs
     */
    public $items_list;
    public $records_list;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'string', 'max' => 80],

            [['records_list', 'items_list'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
        ];
    }

    public function getItems()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->viaTable('template_has_item', ['template_id' => 'id']);
    }

    public function getRecords()
    {
        return $this->hasMany(Record::className(), ['template_id' => 'id']);
    }

    public function behaviors()
    {
        return
            [
                [
                    'class' => \tigokr\multinput\Behavior::className(),
                    'relations' => [
                        'items_list' => ['relation'=>'items', 'order'=>'ord'],
                        'records_list' => ['relation'=>'records', 'delete_related_after_updated'=>true, 'delete_related_after_delete'=>true],
                    ]
                ]
            ];
    }

}
