<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lww
 * Date: 14-8-13
 * Time: 下午4:16
 * To change this template use File | Settings | File Templates.
 */
class FileCloudConfig
{
    //域名不要加"http://"
    const FILE_SERVER_DOMAIN = "minibank.qiniudn.com";

    //系统自身使用的图片、图标等文件，实际部署时，应设置到一个公开的bucket上
    //以便云存储做CDN加速
    const SYS_FILE_BUCKET = 'minibank-me-bucket1';

    //用户产生的数据，应放到私有的bucket上
    const USER_PRIVATE_FILE_BUCKET = 'minibank-me-bucket1';

    //用户产生的数据，但需要广泛访问的，应放到公有的bucket上
    const USER_PUBLIC_FILE_BUCKET = 'minibank-me-bucket1';


    /**
     * 以下是业务数据对应的前缀
     */


}
