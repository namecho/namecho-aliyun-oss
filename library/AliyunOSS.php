<?php
require './aliyun-oss-php-sdk.phar';

use OSS\OssClient;
use OSS\Core\OssException;

class AliyunOSS
{
    private $accessKeyId;
    private $accessKeySecret;
    private $endpoint;
    private $bucket;
    private $ossClient;

    public function __construct($accessKeyId, $accessKeySecret, $endpoint, $bucket)
    {
        $this->accessKeyId = $accessKeyId;
        $this->accessKeySecret = $accessKeySecret;
        $this->endpoint = $endpoint;
        $this->bucket = $bucket;
        $this->init();
    }

    private function init()
    {
        try {
            $this->ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, $this->endpoint);
        } catch (OssException $e) {
            print $e->getMessage();
        }
    }

    /**
     * 字符串上传
     */
    public function putContent($object, $content)
    {
        try {
            $this->ossClient->putObject($this->bucket, $object, $content);
        } catch (OssException $e) {
            echo '<pre>';
            printf($e->getMessage());
            return false;
        }
        return $object;
    }

    /**
     * 文件上传
     */
    public function uploadFile($object, $filePath)
    {
        try {
            $this->ossClient->uploadFile($this->bucket, $object, $filePath);
        } catch (OssException $e) {
            return false;
        }
        return $object;
    }

    /**
     * 文件下载到本地文件
     */
    public function downloadFile($object, $localfile)
    {
        $options = array(
            OssClient::OSS_FILE_DOWNLOAD => $localfile
        );

        try {
            $this->ossClient->getObject($this->bucket, $object, $options);
        } catch (OssException $e) {
            return false;
        }
        return true;
    }

    /**
     * 文件下载到本地内存
     */
    public function getContent($object)
    {
        try {
            $content = $this->ossClient->getObject($this->bucket, $object);
        } catch (OssException $e) {
            return false;
        }
        return $content;
    }

    /**
     * 删除
     */
    public function deleteFile($objects)
    {
        try {
            $this->ossClient->deleteObject($this->bucket, $objects);
        } catch (OssException $e) {
            return false;
        }
        return true;
    }

    /**
     * 文件列表
     */
    public function getFileList($prefix)
    {
        $options = ['prefix' => $prefix];
        try {
            $listObjectInfo = $this->ossClient->listObjects($this->bucket, $options);
        } catch (OssException $e) {
            return false;
        }
        $listObject = $listObjectInfo->getObjectList();
        $listPrefix = $listObjectInfo->getPrefixList();
        $list = [];
        foreach ($listObject as $objectInfo) {
            $list['file'][] = [
                'type' => 'object',
                'name' => $objectInfo->getKey()
            ];
        }
        foreach ($listPrefix as $prefixInfo) {
            $list['dir'][] = [
                'type' => 'prefix',
                'name' => $prefixInfo->getPrefix()
            ];
        }
        return $list;
    }
}
