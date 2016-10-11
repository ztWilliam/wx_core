<?php

class WxApiModule extends CWebModule
{
    private $_assetsUrl;
    public $url;

    public function init()
    {
        // this method is called when the module is being created
        // you may place code here to customize the module or the application

        // import the module-level models and components
        $this->setImport(array(
            'wxApi.components.*',
            'wxApi.components.listeners.*',
            'wxApi.components.apiViewModels.*',
            'wxApi.components.util.*',
            'wxApi.components.util.cloud.*',
            'wxApi.models.*',

        ));
    }

    public function getAssetsUrl()
    {
        if ($this->_assetsUrl === null) {
            $this->_assetsUrl = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('wxApi.assets'));
            return $this->_assetsUrl;
        }
        else
            return $this->_assetsUrl;
    }

    /**
     * @param string $value the util URL that contains all published asset files of gii.
     */
    public function setAssetsUrl($value)
    {
        $this->_assetsUrl = $value;
    }

    public function beforeControllerAction($controller, $action)
    {
        if(parent::beforeControllerAction($controller, $action))
        {
            // this method is called before any module controller action is performed
            // you may place customized code here


            return true;
        }
        else{
            return false;
        }
    }
}
