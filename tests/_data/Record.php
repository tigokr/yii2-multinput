<?php
/**
 * Created for yii2-multi-input
 * in extweb.org with love!
 * Artem Dekhtyar mail@artemd.ru
 * 17.07.2015
 */

namespace data;

class Record extends  \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'record';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['template_id'], 'integer'],
            [['title'], 'string', 'max' => 80],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'template_id' => 'Template Id',
            'title' => 'Title',
        ];
    }
}
