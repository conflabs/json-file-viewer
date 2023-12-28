<?php declare(strict_types=1);

namespace Conflabs\JsonFileViewer\Traits;

trait GoogleDriveHelperTrait
{

    /**
     * @param string $url
     * @return bool
     */
    public static function isGoogleDriveLink(string $url): bool
    {
        return str_contains($url, 'https://drive.google.com/file/d/');
    }

    /**
     * @param string $url
     * @return string
     */
    public static function getGoogleDriveFileId(string $url): string
    {
        $removedPrefixChars = str_replace('https://drive.google.com/file/d/', '', $url);
        $explodeOnForwardSlash = explode('/', $removedPrefixChars);

        return $explodeOnForwardSlash[0];
    }

    /**
     * @param string $fileId
     * @return string|null
     */
    public static function getGoogleDriveFileContentsByFileId(string $fileId): null|string
    {
        $contents = file_get_contents('https://drive.google.com/uc?export=download&id=' . $fileId);
        if (!$contents) {
            return null;
        }

        return $contents;
    }
}