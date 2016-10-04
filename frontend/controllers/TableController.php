<?php
namespace frontend\controllers;

use Yii;
use common\models\agot\Table;

/**
 * Table controller
 * date 2016-08-30
 * author wolfbian
 */
class TableController extends JsonBaseController{

    /**
     * @name  获取桌子列表情况
     * @method GET
     * @author wolfbian
     * @date 2016-09-07
     */
    public function actionTables(){
        $tables = Yii::$app->params['tables'];
        $data = [];
        foreach ($tables as $key => $value) {
            $t = new Table($value);
            $data[] =  $t->getTableInfo();
        }
        return ['code' => self::CODE_SUCCESS, 'data' => $data];
    }

    /**
     * @name  获取桌子详情
     * @method GET
     * @author wolfbian
     * @date 2016-10-03
     */
    public function actionTable(){

        $user_id = Yii::$app->user->id;
        if (empty($user_id)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }
        $table_id = Table::getTableIdByUserId($user_id);
        $t = new Table($table_id);

        return ['code' => self::CODE_SUCCESS, 'data' => $t->info];
    }

    /**
     * @name  洗卡
     * @method POST
     * @author wolfbian
     * @date 2016-10-04
     * @param    int            type (0：手牌，1：牌库，2：弃牌区，3：死亡牌区)
     */
    public function actionShuttleCard(){
        $user_id = Yii::$app->user->id;
        if (empty($user_id)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }
        $table_id = Table::getTableIdByUserId($user_id);
        $table = new Table($table_id);

        $type = intval(Yii::$app->request->post("type"));
        $side = intval(Yii::$app->request->post("side"));

        $ret = $table->shuttle(['type' => $type, 'side' => $side]);

        if ($ret[0] === true) {
            return ['code' => self::CODE_SUCCESS, 'data' => $ret[1]];
        }

        return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => $ret[1]];
    }

    
    /**
     * @name  准备
     * @method POST
     * @param    int            id   桌号
     * @param    int            side 在桌子的哪一边 0 1 ...
     * @param    int            deck_id  使用的牌组id
     * @param    int            game_id  游戏id(默认0，代表冰火)
     * @author wolfbian
     * @date 2016-08-30
     */
    public function actionReady(){
        $user_id = Yii::$app->user->id;
        if (empty($user_id)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }

        $table_id = intval(Yii::$app->request->post("id"));
        $side = intval(Yii::$app->request->post("side"));
        $deck_id = intval(Yii::$app->request->post("deck_id"));
        $game_id = intval(Yii::$app->request->post("game_id", 0));

        if (!in_array($table_id, Yii::$app->params['tables'])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的桌号"];
        }

        if (!in_array($game_id, Yii::$app->params['games'])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的游戏ID"];
        }

        if (!in_array($side, Yii::$app->params['game_sides'][$game_id])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的桌边"];
        }

        $table = new Table($table_id);

        if( !$table->ready(['user_id' => $user_id, 'game_id' => $game_id,  'side' => $side, 'deck_id' => $deck_id]) ){
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "系统错误"];
        }

        return ['code' => self::CODE_SUCCESS];
    }

    /**
     * @name  取消准备
     * @method POST
     * @param    int            id   桌号
     * @param    int            side 在桌子的哪一边 0 1 ...
     * @author wolfbian
     * @date 2016-08-31
     */
    public function actionUnReady(){
        $user_id = Yii::$app->user->id;
        if (empty($user_id)) {
            return ['code' => self::CODE_NOLOGIN, 'msg' => "未登录"];
        }

        $table_id = intval(Yii::$app->request->post("id"));
        $side = intval(Yii::$app->request->post("side"));

        if (!in_array($table_id, Yii::$app->params['tables'])) {
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "不合法的桌号"];
        }

        $table = new Table($table_id);

        if( !$table->unready(['user_id' => $user_id,  'side' => $side]) ){
            return ['code' => self::CODE_SYSTEM_ERROR, 'msg' => "系统错误"];
        }

        return ['code' => self::CODE_SUCCESS];
    }

}