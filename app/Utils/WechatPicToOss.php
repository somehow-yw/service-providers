<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/6 0006
 * Time: 上午 10:53
 */

namespace App\Utils;

use App\Exceptions\Feedback\FeedbackException;
use Storage;

class WechatPicToOss
{
    // 可上传的文件（图片）类型
    private $types = [
        'image/bmp'  => '.bmp',
        'image/gif'  => '.gif',
        'image/jpeg' => '.jpg',
        'image/png'  => '.png',
    ];

    public function __construct()
    {
    }

    /**
     * 检查目录的方法
     *
     * @param $path string 要检查的目录路径
     *
     * @return bool
     */
    private function path_censor($path)
    {
        //检查目录是否存在，如不存在则建立
        if (!is_dir($path)) {
            if (!@mkdir($path, 0755, true)) {
                return false;
            }
        }
        //检查目录是否可写
        if (!@is_writable($path)) {
            return false;
        }

        return true;
    }

    /**
     * 将本地图片上传到OSS
     *
     * @param        $path      string OSS上保存的地址
     * @param        $localPath string 本地图片的地址
     * @param        $logFile   string 日志路径                        
     * @param string $ossType   OSS类型
     *
     * @throws FeedbackException
     * @return string
     */
    public function wechatFileUpload($path, $localPath, $logFile, $ossType = 'aliyun')
    {
        $disk = Storage::disk($ossType);
        $name = $disk->putFile($path, $localPath);

        if (!$name) {
            $messages = "上传图片到OSS上失败：{$path},本地地址为：{$localPath}";
            fileLogWrite($messages, $logFile);
        }
        
        return 'ok';
    }

    /**
     * 采集微信临时文件到本地 使用微信媒体的media_id下载文件
     *
     * @access public
     *
     * @param string $accessToken 微信接口调用凭证
     * @param string $mediaId     远程ID
     * @param string $filePath    希望本地保存文件路径(结尾不需要‘/与文件名’)
     * @param string $logFile     日志文件保存路径 如：storage_path('logs/ximu_'.date('Ymd') . '.log')
     *                   
     * @param string $fileName    希望保存的文件名 空为 $mediaId
     * @param bool   $reLoade     是否强制重新获取
     * @param string $fileType    希望保存成的文件类型(扩展名) 如：jpg
     *
     * @return string 
     * @throws FeedbackException
     */
    public function weCurlDownload(
        $accessToken,
        $mediaId,
        $filePath,
        $logFile,
        $fileName = '',
        $reLoade = false,
        $fileType = 'jpg'
    ) {
        //检查路径是否存在，不存在则新建
        $localFilePath = storage_path('app') . "/wechatImages/{$filePath}";
        if (!$this->path_censor($localFilePath)) {
            throw new FeedbackException('图片保存路径创建失败', FeedbackException::PATH_CREATE_FAIL);
        }
        $fileName = empty($fileName) ? $mediaId : $fileName;
        // 本地文件的真实保存路径
        $localFileSaveName = $localFilePath . '/' . $fileName . ".{$fileType}";
       
        if ($reLoade) {
            @unlink($localFileSaveName);
        }
        $messages = "去微信拉取图片并保存为：{$localFileSaveName}";
        fileLogWrite($messages, $logFile);
        $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token={$accessToken}&media_id={$mediaId}";
        $cp = curl_init($url);
        $fp = fopen($localFileSaveName, "w");
        curl_setopt($cp, CURLOPT_FILE, $fp);
        curl_setopt($cp, CURLOPT_HEADER, 0);
        curl_exec($cp);  //获取curl返回信息
        $httpInfo = curl_getinfo($cp);  //获取curl连接句柄的信息
        //判断响应首部里的的content-type的值是否是允许的类型
        if (empty($this->types[$httpInfo['content_type']])) {
            unlink($localFileSaveName);
            throw new FeedbackException('微信图片拉取失败', FeedbackException::DOWNLOAD_FAIL);
        }
        curl_close($cp);
        fclose($fp);

        return $localFileSaveName;
    }

    /**
     * 下载微信图片到本地保存
     *
     * @param $fileMediaId string 远程ID
     * @param $logFile     string 日志文件保存路径 如：storage_path('logs/ximu_'.date('Ymd') . '.log')
     * @param $filePath    string 希望本地保存文件路径(开始需要‘/’，结尾不需要‘/与文件名’)
     * @param $fileName    string 希望保存的文件名,不带扩展名 空为 $fileMediaId
     * @param $fileType    string 文件扩展名
     * @param $reLoad      bool 如果图片已存在，是否再次强制获取图片
     *
     * @return string 
     */
    public function wechatFileDownload(
        $fileMediaId,
        $logFile,
        $filePath,
        $fileName = '',
        $fileType = 'jpg',
        $reLoad = true
    ) {
        $accessTokenObj = \DB::table('dp_weInterface')
                             ->where('interfaceName', 'access_token')
                             ->first();
        $filePathArr = $this->weCurlDownload(
            $accessTokenObj->interfaceCode,
            $fileMediaId,
            $filePath,
            $logFile,
            $fileName,
            $reLoad,
            $fileType
        );

        return $filePathArr;
    }
}