<?php

namespace saowx\lib;

/**
 * error code 说明.
 * <ul>
 *    <li>-41001: encodingAesKey 非法</li>
 *    <li>-41003: aes 解密失败</li>
 *    <li>-41004: 解密后得到的buffer非法</li>
 *    <li>-41005: base64加密失败</li>
 *
 *    <li>-90000: 网络连接故障</li>
 *
 *    <li>-96102: 签名校验失败</li>
 *    <li>-96103: 无效数据</li>
 *    <li>-96104: 缺少字段</li>
 *    <li>-96105: 微信api请求失败</li>
 *
 *    <li>-41016: base64解密失败</li>
 *    <li>-41016: base64解密失败</li>
 *    <li>-41016: base64解密失败</li>
 *    <li>-41016: base64解密失败</li>
 * </ul>
 */
class ErrorClass
{
    public static $status;
    public static $CLIENT = 50009;
    public static $SIGN = 96102;
    public static $INVALIDDATA = 96103;
    public static $FIELDLACK = 96104;
    public static $WXERROR = 96105;

}

?>