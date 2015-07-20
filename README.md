# yii2-multinput

Based on https://github.com/unclead/yii2-multiple-input

## View 

    <?php
    $add_js = <<< SCRIPT
    function(){
        var w = $(this),
        line = w.find('.multiple-input-list__item:last');

        line.find('.price-selector').bind('change', function(){
            var price = $(this).find(":selected").data('price');
            var field = $(this).closest('.multiple-input-list__item').find('.quantity-selector');
            field.val(price);
        })
    }
    SCRIPT;
    $price_options = \yii\helpers\ArrayHelper::map(\common\models\Price::find()->select(['id', 'data'=>'price'])->asArray()->all(), 'id', 'data');
    array_walk($price_options, function(&$val){
        $val = ['data-price'=>$val];
    });

    echo $form->field($model, 'items_list')->widget(\tigokr\multinput\Widget::className(), [
        'columns' => [
            [
                'name'=>'id',
                'type'=>\tigokr\multinput\Column::TYPE_HIDDEN_INPUT,
            ],
            [
                'name' => 'price_id',
                'type' => \tigokr\multinput\Column::TYPE_DROPDOWN,
                'title' => 'Price',
                'items' => \yii\helpers\ArrayHelper::map(\common\models\Price::find()->all(), 'id', 'title'),
                'options' => [
                    'class'=>'price-selector',
                    'options' => $price_options,
                ],
            ],
            [
                'name' => 'quantity',
                'title' => \Yii::t('app', 'Quantity'),
                'defaultValue' => reset($price_options)['data-price'],
                'options' => [
                    'class'=>'quantity-selector',
                ],
            ],
        ]
        ,
        /**
         * [init|addNewRow|removeRow]
         */
        'events' => [
            'addNewRow' => $add_js,
        ],
    ]); ?>
or
    
    <?php echo $form->field($model, 'emails')->widget(\tigokr\multinput\Widget::className(), [
        'max'=>10,
        'initial_count'=>3,
        'min'=>1,
    ]); ?>
    
## Model 
    ...
    public $items_list;
    ...
    public function rules() {
        return [
            ...
            [['items_list'], 'safe'],
        ];
    }
    ...
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getItems()
    {
        return $this->hasMany(EstimateItem::className(), ['estimate_id' => 'id']);
    }
    ...
    public function behaviors() {
        return
            [
                [
                    'class' => \tigokr\multinput\Behavior::className(),
                    'relations' => [
                        'items_list' => ['relation'=>'items'],
                    ]
                ]
            ];
    }
    ...
