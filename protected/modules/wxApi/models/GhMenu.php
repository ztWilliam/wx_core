<?php

Yii::import('application.modules.wxApi.components.*');
Yii::import('application.modules.wxApi.components.util.*');

/**
 * This is the model class for table "wxa_gh_menu".
 *
 * The followings are the available columns in table 'wxa_gh_menu':
 * @property integer $id
 * @property string $menuName
 * @property string $menuType
 * @property string $menuKey
 * @property integer $ghId
 * @property string $classAlias
 * @property string $handlerType
 * @property integer $parentMenu
 * @property integer $displayOrder
 */
class GhMenu extends CActiveRecord
{
	/**
	 * Returns the static model of the specified GhMenu class.
	 * @return GhMenu the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

    /**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'wxa_gh_menu';
	}

    /**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('menuName, menuType, menuKey, ghId, classAlias, parentMenu, displayOrder', 'required'),
			array('ghId, parentMenu, displayOrder', 'numerical', 'integerOnly'=>true),
			array('menuName, menuType', 'length', 'max'=>10),
			array('menuKey', 'length', 'max'=>256),
			array('classAlias', 'length', 'max'=>100),
			array('handlerType', 'length', 'max'=>5),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, menuName, menuType, menuKey, ghId, classAlias, handlerType, parentMenu, displayOrder', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'menuName' => 'Menu Name',
			'menuType' => 'Menu Type',
			'menuKey' => 'Menu Key',
			'ghId' => 'Gh',
			'classAlias' => 'Class Alias',
            'handlerType' => 'Handler Type',
			'parentMenu' => 'Parent Menu',
			'displayOrder' => 'Display Order',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('menuName',$this->menuName,true);
		$criteria->compare('menuType',$this->menuType,true);
		$criteria->compare('menuKey',$this->menuKey,true);
		$criteria->compare('ghId',$this->ghId);
		$criteria->compare('classAlias',$this->classAlias,true);
		$criteria->compare('handlerType',$this->handlerType,true);
		$criteria->compare('parentMenu',$this->parentMenu);
		$criteria->compare('displayOrder',$this->displayOrder);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    const MENU_TYPE_MAIN_MENU = 'main';
    const MENU_KEY_NOT_NEED = 'none';
    const CLASS_ALIAS_MAIN_MENU = 'none';
    const MENU_URL_NOT_NEED = 'none';
    const PARENT_MENU_MAIN_MENU = 0;

    const DISPLAY_ORDER_STEP = 100;
    const MAIN_MENU_MAX_COUNT = 3;
    const SUB_MENU_MAX_COUNT = 5;

    const MAIN_MENU_MAX_LENGTH = 4;
    const SUB_MENU_MAX_LENGTH = 7;

    public static function createMainMenu($ghId, $menuName, $afterMenu)
    {
        if(empty($ghId)){
            throw new WxException('请输入公众号的id');
        }
        if(empty($menuName)){
            throw new WxException('请输入新菜单的名称');
        }
        if(mb_strlen($menuName, 'utf8') > self::MAIN_MENU_MAX_LENGTH) {
            throw new WxException('主菜单的名称不得超过'. self::MAIN_MENU_MAX_LENGTH .'个字');
        }

        //获得所有mainMenu，以menuName为key
        $allMainMenu = self::findAllMainMenus($ghId);

        // 检查主菜单名称是否有重复：
        if(isset($allMainMenu[$menuName])) {
            throw new WxException('已经有名为 '.$menuName . ' 的主菜单了');
        }

        if(count($allMainMenu) >= self::MAIN_MENU_MAX_COUNT) {
            throw new WxException('主菜单不能超过' . self::MAIN_MENU_MAX_COUNT . '个');
        }

        if(!empty($afterMenu)) {
            $afterMenuObj = $allMainMenu[$afterMenu];
            if(!isset($afterMenuObj)) {
                throw new WxException('要插入其后的主菜单不存在');
            }

            // 将$afterMenu后面的displayOrder 分别 都加上 DISPLAY_ORDER_STEP 并保存
            self::increaseDisplayOrderAfter($afterMenuObj->displayOrder, $ghId, self::PARENT_MENU_MAIN_MENU);

            $displayOrder = $afterMenuObj->displayOrder + self::DISPLAY_ORDER_STEP;
        } else {
            // 将所有已存在的主菜单的displayOrder 都加上 DISPLAY_ORDER_STEP 并保存
            self::increaseDisplayOrderAfter(0, $ghId, self::PARENT_MENU_MAIN_MENU);

            $displayOrder = self::DISPLAY_ORDER_STEP;
        }

        $newMenu = self::createMenuObject($ghId, $menuName,
            self::MENU_TYPE_MAIN_MENU, self::MENU_KEY_NOT_NEED,
            self::PARENT_MENU_MAIN_MENU, $displayOrder, self::CLASS_ALIAS_MAIN_MENU, WxCommonDef::HANDLER_TYPE_NONE);

        return $newMenu;

    }

    private static function createMenuObject($ghId, $menuName, $menuType,
                                             $menuKey, $parentMenu, $displayOrder, $classAlias, $handlerType)
    {
        $menuObj = new GhMenu();
        $menuObj->ghId = $ghId;
        $menuObj->menuName = $menuName;
        $menuObj->menuType = $menuType;

        $menuObj->menuKey = $menuKey;
        $menuObj->parentMenu = $parentMenu;
        $menuObj->displayOrder = $displayOrder;
        $menuObj->classAlias = $classAlias;
        $menuObj->handlerType = $handlerType;

        if(!$menuObj->save()){
            LogWriter::logModelSaveError($menuObj, __METHOD__, array(
                'ghId' => $ghId,
                'menuName' => $menuName,
                'menuType' => $menuType,
                'menuKey' => $menuKey,
                'displayOrder' => $displayOrder,
                'classAlias' => $classAlias,
                'handlerType' => $handlerType,
            ));
            throw new Exception('公众号菜单项保存失败');
        }

        return $menuObj;
    }

    private static function findAllMainMenus($ghId)
    {
        $criteria = new CDbCriteria();
        $criteria->order = ' displayOrder asc ';
        //获得所有mainMenu，以menuName为key
        $allMenus = self::model()->findAllByAttributes(array('ghId' => $ghId,
            'parentMenu' => self::PARENT_MENU_MAIN_MENU), $criteria);
        $result = array();

        foreach($allMenus as $menuObj) {
            $result[$menuObj->menuName] = $menuObj;
        }

        return $result;
    }

    private static function increaseDisplayOrderAfter($afterOrder, $ghId, $parentMenu)
    {
        $sql = 'update ' . self::tableName(). ' set displayOrder = displayOrder + ' . self::DISPLAY_ORDER_STEP . ' ' .
            'where ghId = '. $ghId . ' and parentMenu = ' . $parentMenu . ' and displayOrder > ' . $afterOrder ;

        $cmd = self::model()->getDbConnection()->createCommand($sql);

        $cmd->execute();
    }

    private static function decreaseDisplayOrder($afterOrder, $ghId, $parentMenu)
    {
        $sql = 'update ' . self::tableName(). ' set displayOrder = displayOrder - ' . self::DISPLAY_ORDER_STEP . ' ' .
            'where ghId = '. $ghId . ' and parentMenu = ' . $parentMenu . ' and displayOrder > ' . $afterOrder ;

        $cmd = self::model()->getDbConnection()->createCommand($sql);

        $cmd->execute();
    }


    public static function createSubMenu($ghId, $menuName, $parentName, $afterMenu, $eventType, $menuKey, $url, $handler)
    {
        if(empty($ghId)){
            throw new WxException('请输入公众号的id');
        }
        if(empty($menuName)){
            throw new WxException('请输入新菜单的名称');
        }
        if(mb_strlen($menuName, 'utf8') > self::SUB_MENU_MAX_LENGTH) {
            throw new WxException('子菜单的名称不得超过'. self::SUB_MENU_MAX_LENGTH .'个字');
        }

        if(empty($parentName)){
            throw new WxException('请输入主菜单名称');
        }
        if(empty($eventType) || ($eventType !== 'click' && $eventType !== 'view')){
            throw new WxException('请输入菜单类型：click | view');
        }

        $key = '';
        if($eventType == 'click' ){
            if(empty($menuKey)){
                throw new WxException('click类型的菜单必须输入key');
            }
            $key = $menuKey;
        }
        if($eventType == 'view'){
            if(empty($url)){
                throw new WxException('view类型的菜单必须输入url');
            }
            $key = $url;
        }

        if(empty($handler)) {
            throw new WxException('请输入菜单事件发生时的处理类');
        }
        //检查handler是否有效
        $handlerType = WxCommonFunction::checkHandler($handler);

        if(self::existKey($ghId, $key)) {
            throw new WxException('菜单的key或url已经存在');
        }

        $parentMenuObj = self::findMainMenu($ghId, $parentName);
        if(!isset($parentMenuObj)){
            throw new WxException('主菜单不存在');
        }

        //获得所有mainMenu，以menuName为key
        $allSubMenu = self::findAllSubMenus($ghId, $parentMenuObj);

        // 检查菜单名称是否有重复：
        if(isset($allSubMenu[$menuName])) {
            throw new WxException('已经有名为 '.$menuName . ' 的菜单了');
        }

        if(count($allSubMenu) >= self::SUB_MENU_MAX_COUNT) {
            throw new WxException('子菜单不能超过' . self::SUB_MENU_MAX_COUNT . '个');
        }

        if(!empty($afterMenu)) {
            $afterMenuObj = $allSubMenu[$afterMenu];
            if(!isset($afterMenuObj)) {
                throw new WxException('要插入其后的子菜单不存在');
            }

            // 将$afterMenu后面的displayOrder 分别 都加上 DISPLAY_ORDER_STEP 并保存
            self::increaseDisplayOrderAfter($afterMenuObj->displayOrder, $ghId, $parentMenuObj->id);

            $displayOrder = $afterMenuObj->displayOrder + self::DISPLAY_ORDER_STEP;
        } else {
            // 将所有已存在的主菜单的displayOrder 都加上 DISPLAY_ORDER_STEP 并保存
            self::increaseDisplayOrderAfter(0, $ghId, $parentMenuObj->id);

            $displayOrder = self::DISPLAY_ORDER_STEP;
        }

        $newMenu = self::createMenuObject($ghId, $menuName,
            $eventType, $key, $parentMenuObj->id, $displayOrder, $handler, $handlerType);

        return $newMenu;

    }

    private static function findMainMenu($ghId, $menuName)
    {
        $menuObj = self::model()->findByAttributes(array('ghId'=>$ghId,
            'menuName' => $menuName, 'menuType' => self::MENU_TYPE_MAIN_MENU));

        return $menuObj;
    }

    private static function findAllSubMenus($ghId, $parentMenuObj)
    {
        $allMenus = self::model()->findAllByAttributes(array('ghId' => $ghId, 'parentMenu' => $parentMenuObj->id));
        $result = array();

        foreach($allMenus as $menuObj) {
            $result[$menuObj->menuName] = $menuObj;
        }

        return $result;

    }

    private static function existKey($ghId, $key)
    {
        $menuObj = self::model()->findByAttributes(array('ghId' => $ghId, 'menuKey' => $key));

        if(isset($menuObj)) {
            return true;
        } else {
            return false;
        }
    }

    public static function removeSubMenu($ghId, $menuName, $parentName)
    {
        if(empty($ghId)){
            throw new WxException('请输入公众号的id');
        }
        if(empty($menuName)){
            throw new WxException('请输入待删除子菜单的名称');
        }
        if(empty($parentName)){
            throw new WxException('请输入主菜单名称');
        }

        $parentMenuObj = self::findMainMenu($ghId, $parentName);
        if(!isset($parentMenuObj)){
            throw new WxException('主菜单不存在');
        }

        $menuObj = self::findSubMenu($ghId, $parentMenuObj->id, $menuName);
        if(!isset($menuName)){
            throw new WxException('待删除子菜单不存在');
        }

        // 将后面的菜单的displayOrder调减：
        self::decreaseDisplayOrder($menuObj->displayOrder, $ghId, $parentMenuObj->id );


        //删除该menu：
        $rows = self::model()->deleteByPk($menuObj->id);
        if($rows !== 1){
            throw new Exception('删除菜单时出错,菜单：' . $menuName . ' in ghId ' . $ghId);
        }

    }

    public static function removeMainMenu($ghId, $menuName)
    {
        if(empty($ghId)){
            throw new WxException('请输入公众号的id');
        }
        if(empty($menuName)){
            throw new WxException('请输入待删除主菜单的名称');
        }


        $menuObj = self::findMainMenu($ghId, $menuName);
        if(!isset($menuName)){
            throw new WxException('待删除主菜单不存在');
        }

        // 将后面的菜单的displayOrder调减：
        self::decreaseDisplayOrder($menuObj->displayOrder, $ghId, self::PARENT_MENU_MAIN_MENU );


        //删除该菜单下的所有子菜单：
        self::deleteAllSubMenu($ghId, $menuObj->id);

        //删除该menu：
        $rows = self::model()->deleteByPk($menuObj->id);
        if($rows !== 1){
            throw new Exception('删除菜单时出错,菜单：' . $menuName . ' in ghId ' . $ghId);
        }
    }

    private static function findSubMenu($ghId, $parentId, $menuName)
    {
        return self::model()->findByAttributes(array('ghId'=>$ghId, 'parentMenu' => $parentId, 'menuName' => $menuName));
    }

    private static function deleteAllSubMenu($ghId, $parentId)
    {
        self::model()->deleteAllByAttributes(array('ghId' => $ghId, 'parentMenu' => $parentId));
    }

    public static function updateMenuOnline($ghId)
    {
        if(empty($ghId)) {
            throw new WxException('请输入公众号的id');
        }

        $menuJson = self::menuToJson($ghId);

        $accessToken = GhAccessToken::getAccessToken($ghId);

        WxCgiCaller::deleteMenu($accessToken);
        WxCgiCaller::createMenu($accessToken, $menuJson);

        return $menuJson;
    }

    private static function menuToJson($ghId)
    {
        $criteria = new CDbCriteria();
        $criteria->order = ' displayOrder asc ';
        //获得所有mainMenu，以menuName为key
        $allMenus = self::model()->findAllByAttributes(array('ghId' => $ghId,
            'parentMenu' => self::PARENT_MENU_MAIN_MENU), $criteria);

        $result = array();
        foreach($allMenus as $mainMenuItem) {
            $subMenus = self::model()->findAllByAttributes(array('ghId' => $ghId,
                'parentMenu' => $mainMenuItem->id), $criteria);
            $subResult = array();
            foreach($subMenus as $subMenuItem){
                $subResult[] = self::itemToJsonObj($subMenuItem);
            }

            //一组菜单的json对象生成：
            $result[] = array(
                'name' => $mainMenuItem->menuName,
                'sub_button' => $subResult,
            );
        }

        $menuArray = array('button' => $result);

        return FastJSON::encode($menuArray);
    }

    private static function itemToJsonObj($subMenuItem)
    {
        if($subMenuItem->menuType == 'click'){
            return array(
                'name' => $subMenuItem->menuName,
                'type' => $subMenuItem->menuType,
                'key' => $subMenuItem->menuKey,
            );
        } elseif($subMenuItem->menuType == 'view') {
            return array(
                'name' => $subMenuItem->menuName,
                'type' => $subMenuItem->menuType,
                'url' => $subMenuItem->menuKey,
            );
        } else {
            throw new Exception('不能识别的菜单类型：' . $subMenuItem->menuType .
                ', 菜单id：' . $subMenuItem->id);
        }
    }


    public static function processMenuEvent($ghInfo, $eventObj)
    {
        $event = $eventObj->Event;
        if($event != 'CLICK' && $event != 'VIEW') {
            Yii::log('处理菜单事件时发生错误：不可识别的事件类型：' .$event . ', 消息体：' . $eventObj , 'error');
            throw new Exception('不可识别的菜单事件');
        }

        Yii::log(__METHOD__ . '处理菜单事件', 'warning');
        $eventKey = $eventObj->EventKey;

        $menuObj = self::model()->findByAttributes(array('ghId' => $ghInfo->id, 'menuKey' => $eventKey));

        if(!isset($menuObj)) {
            throw new WxException('菜单已失效');
        }
        if($menuObj->classAlias == self::CLASS_ALIAS_MAIN_MENU){
            throw new Exception('菜单的响应类未设置：'. $eventObj->EventKey. ', 公号id：' . $ghInfo->id);
        }


        //根据handlerType和classAlias的设定，调用相应的处理程序：
        $result = WxCommonFunction::callEventHandler($menuObj->classAlias, $menuObj->handlerType,
            $eventObj);

        return $result;

    }

    public static function removeAll($ghId)
    {
        if(empty($ghId)) {
            throw new WxException('请输入公众号的id');
        }

        self::model()->deleteAllByAttributes(array('ghId' => $ghId));

        $accessToken = GhAccessToken::getAccessToken($ghId);

        WxCgiCaller::deleteMenu($accessToken);

    }


}