<?php

use \Codeception\Util\Debug as d;

class BehaviorTest extends \yii\codeception\TestCase
{

    public $appConfig = '@tests/unit/_config.php';


    /**
     * @param $class string
     * @param null $id integer
     * @param $post []
     * @return \yii\db\ActiveRecord
     */
    private function save($class, $id = null, $post){
        $class = 'data\\'.$class;
        if(!empty($id))
            $model = $class::findOne($id);
        else
            $model = new $class;

        $this->assertTrue($model->load($post), 'Load POST data');
        $this->assertTrue($model->save(), 'Save model');

        return $model;
    }

    /**
     * @param $class string
     * @param $id integer
     * @return \yii\db\ActiveRecord
     */
    private function reload($class, $id){
        $class = 'data\\'.$class;

        $model = $class::findOne($id);
        $this->assertNotEmpty($model, 'Reload model');
        return $model;
    }

    // tests
    public function testAddAndDeleteNewSimpleObject()
    {
        $model = $this->save('Template', null, ['Template'=>[
            'title'=>'New template title',
        ]]);

        $model = $this->reload('Template', $model->id);
        $this->assertEquals(1, $model->delete(), 'Deleting new simple model');
    }

    public function testTryToGetNonExistenceRelation(){

        $model = $this->reload('Template', 3);

        $this->assertEmpty($model->records, 'Empty relation');
        $this->assertEmpty($model->records_list, 'Empty relation list');
    }


    public function testSaveNewObjectOneToMany(){
        $model = $this->save('Template', null, ['Template'=>[
            'title'=>'Test template',
        ]]);

        $this->assertEquals(0, count( $model->records ), 'Records count after save one-to-many relation');
        $this->assertEquals(1, $model->delete(), 'Deleting new model');

        $model = $this->save('Template', null, ['Template'=>[
            'title'=>'Test template',
            'records_list'=>[
                ['title'=>'New first record title'],
                ['title'=>'New second record title'],
            ],
        ]]);
        $this->assertEquals(2, count( $model->records ), 'Records count after save one-to-many relation');

        $model = $this->save('Template', $model->id, ['Template'=>[
            'title'=>'Test template 2',
        ]]);
        $this->assertEquals(2, count( $model->records ), 'Records count after save with empty related');

        $model = $this->save('Template', $model->id, ['Template'=>[
            'title'=>'Test template',
            'records_list'=>[],
        ]]);

        $this->assertEquals(0, count( $model->records ), 'Records count after save one-to-many relation');
        $this->assertEquals(1, $model->delete(), 'Deleting new model');
    }

    public function testSaveNewObjectManyToMany(){
        $model = $this->save('Template', null, ['Template'=>[
            'title'=>'Test',
        ]]);

        $this->assertEquals(0, count( $model->items ), 'Records count after save many-to-many relation');
        $this->assertEquals(1, $model->delete(), 'Deleting new model');

        $model = $this->save('Template', null, ['Template'=>[
            'title'=>'Test',
            'items_list'=>[
                ['title'=>'F'],
                ['title'=>'S'],
            ],
        ]]);

        $this->assertEquals(2, count( $model->items ), 'Records count after save many-to-many relation');
        $model = $this->save('Template', $model->id, ['Template'=>[
            'title'=>'Test 2',
        ]]);

        $model = $this->save('Template', $model->id, ['Template'=>[
            'title'=>'Test',
            'items_list'=>[],
        ]]);
        $this->assertEquals(0, count( $model->items ), 'Records count after save many-to-many relation');
        $this->assertEquals(1, $model->delete(), 'Deleting new model');
    }


}