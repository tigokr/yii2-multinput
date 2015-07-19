<?php
/**
 * Created for yii2-multi-input
 * in extweb.org with love!
 * Artem Dekhtyar mail@artemd.ru
 * 17.07.2015
 */

namespace data;

class Item extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'string', 'max' => 80]
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
}

