<?php

namespace OSS\Result;

use OSS\Core\OssUtil;
use OSS\Model\ObjectInfo;
use OSS\Model\ObjectListInfoV2;
use OSS\Model\PrefixInfo;

/**
 * Class ListObjectsV2Result
 * @package OSS\Result
 */
class ListObjectsV2Result extends Result
{
    /**
     * Parse the xml data returned by the ListObjectsV2 interface
     *
     * return ObjectListInfoV2
     */
    protected function parseDataFromResponse()
    {
        $xml = new \SimpleXMLElement($this->rawResponse->body);
				$header = $this->rawResponse->header;
        $encodingType = isset($xml->EncodingType) ? strval($xml->EncodingType) : "";
        $objectList = $this->parseObjectList($xml, $encodingType, $header);
        $prefixList = $this->parsePrefixList($xml, $encodingType);
        $bucketName = isset($xml->Name) ? strval($xml->Name) : "";
        $prefix = isset($xml->Prefix) ? strval($xml->Prefix) : "";
        $prefix = OssUtil::decodeKey($prefix, $encodingType);
        $maxKeys = isset($xml->MaxKeys) ? intval($xml->MaxKeys) : 0;
        $delimiter = isset($xml->Delimiter) ? strval($xml->Delimiter) : "";
        $delimiter = OssUtil::decodeKey($delimiter, $encodingType);
        $isTruncated = isset($xml->IsTruncated) ? strval($xml->IsTruncated) : "";
        $continuationToken = isset($xml->ContinuationToken) ? strval($xml->ContinuationToken) : "";
        $nextContinuationToken = isset($xml->NextContinuationToken) ? strval($xml->NextContinuationToken) : "";
        $startAfter = isset($xml->StartAfter) ? strval($xml->StartAfter) : "";
        $startAfter =  OssUtil::decodeKey($startAfter, $encodingType);
        $keyCount = isset($xml->KeyCount) ? intval($xml->KeyCount) : 0;
        return new ObjectListInfoV2($bucketName, $prefix, $maxKeys, $delimiter, $isTruncated, $objectList, $prefixList, $continuationToken, $nextContinuationToken, $startAfter, $keyCount);
    }

    private function parseObjectList($xml, $encodingType, $header)
    {
        $retList = array();
        if (isset($xml->Contents)) {
            foreach ($xml->Contents as $content) {
                $key = isset($content->Key) ? strval($content->Key) : "";
                $key = OssUtil::decodeKey($key, $encodingType);
                $lastModified = isset($content->LastModified) ? strval($content->LastModified) : "";
                $eTag = isset($content->ETag) ? strval($content->ETag) : "";
                $type = isset($content->Type) ? strval($content->Type) : "";
                $size = isset($content->Size) ? strval($content->Size) : "0";
                $storageClass = isset($content->StorageClass) ? strval($content->StorageClass) : "";
                $retList[] = new ObjectInfo($key, $lastModified, $eTag, $type, $size, $storageClass, $header);
            }
        }
        return $retList;
    }

    private function parsePrefixList($xml, $encodingType)
    {
        $retList = array();
        if (isset($xml->CommonPrefixes)) {
            foreach ($xml->CommonPrefixes as $commonPrefix) {
                $prefix = isset($commonPrefix->Prefix) ? strval($commonPrefix->Prefix) : "";
                $prefix = OssUtil::decodeKey($prefix, $encodingType);
                $retList[] = new PrefixInfo($prefix);
            }
        }
        return $retList;
    }
}
