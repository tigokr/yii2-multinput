<?php

/**
 * @link https://github.com/unclead/yii2-multiple-input
 * @copyright Copyright (c) 2014 unclead
 * @license https://github.com/unclead/yii2-multiple-input/blob/master/LICENSE.md
 */

namespace tigokr\multinput\assets;

use yii\web\AssetBundle;

/**
 * Class MultipleInputAsset
 * @package unclead\widgets\assets
 */
class Asset extends AssetBundle
{
    public $css = [
        'css/multiple-input.css'
    ];

    public $js = [
        'js/jquery.multipleInput.js'
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];

    public function init()
    {

        $this->publishOptions = ['forceCopy' => YII_ENV_DEV];
        $this->sourcePath = __DIR__ . '/src/';
        parent::init();
    }


} 