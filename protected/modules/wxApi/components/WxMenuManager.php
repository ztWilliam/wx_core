<?php
/**
 * Created by JetBrains PhpStorm.
 * User: william
 * Date: 14-8-16
 * Time: 下午8:47
 * To change this template use File | Settings | File Templates.
 */

class WxMenuManager {

    public static function addMainMenu($params)
    {
        try{
            $ghId = trim($params['ghId']);
            $menuName = trim($params['menuName']);
            $afterMenu = trim($params['afterMenu']);

            $menu = GhMenu::createMainMenu($ghId, $menuName, $afterMenu);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '成功', array());

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('添加主菜单失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '添加主菜单失败，请稍后重试', array()
            );
        }
    }

    public static function addSubMenu($params)
    {
        try{
            $ghId = trim($params['ghId']);
            $menuName = trim($params['menuName']);
            $parentName = trim($params['parentName']);
            $afterMenu = trim($params['afterMenu']);
            $eventType = trim($params['eventType']);
            $menuKey = trim($params['menuKey']);
            $url = trim($params['url']);
            $handler = trim($params['handler']);

            $menu = GhMenu::createSubMenu($ghId, $menuName, $parentName, $afterMenu, $eventType, $menuKey, $url, $handler);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '成功', array());

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('添加子菜单失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '添加子菜单失败，请稍后重试', array()
            );
        }
    }

    public static function removeSubMenu($params)
    {
        try{
            $ghId = trim($params['ghId']);
            $menuName = trim($params['menuName']);
            $parentName = trim($params['parentName']);

            GhMenu::removeSubMenu($ghId, $menuName, $parentName);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '成功', array());

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('移除子菜单失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '移除子菜单失败，请稍后重试', array()
            );
        }
    }

    public static function removeMainMenu($params)
    {
        try{
            $ghId = trim($params['ghId']);
            $menuName = trim($params['menuName']);

            GhMenu::removeMainMenu($ghId, $menuName);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS, '成功', array());

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('移除子菜单失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '移除子菜单失败，请稍后重试', array()
            );
        }
    }

    public static function refreshMenu($params)
    {
        try{
            $ghId = trim($params['ghId']);

            $newMenuJson = GhMenu::updateMenuOnline($ghId);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '微信菜单刷新成功。由于微信客户端缓存，需要24小时微信客户端才会展现出来，如需验证，可先取消关注后，再次关注公众号，可以看到创建后的效果',
                array($newMenuJson));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('刷新微信菜单失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '刷新微信菜单失败，请稍后重试', array()
            );
        }

    }

    public static function removeAllMenus($params)
    {
        try{
            $ghId = trim($params['ghId']);

            GhMenu::removeAll($ghId);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '菜单删除完毕',
                array());

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('删除微信菜单失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '删除微信菜单失败，请稍后重试', array()
            );
        }

    }

    public static function getMenusOnline($params)
    {
        try{
            $ghId = trim($params['ghId']);

            $accessToken = GhAccessToken::getAccessToken($ghId);

            $menus = WxCgiCaller::getMenuSetting($accessToken);

            return new ApiResponseData(ApiCommonDef::OPERATION_RESULT_SUCCESS,
                '成功',
                array($menus));

        } catch (WxException $ex) {
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                $ex->getMessage(), array()
            );
        } catch (Exception $ex) {
            Yii::log('删除微信菜单失败:' . $ex->getMessage(), 'error');
            return new ApiResponseData(
                ApiCommonDef::OPERATION_RESULT_FAILED,
                '删除微信菜单失败，请稍后重试', array()
            );
        }

    }
}