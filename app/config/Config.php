<?php
class Config
{
    public const APP_NAME = 'Taratib';
    public const DEFAULT_CHARSET = 'windows-1256';

    public static function setEncoding(): void
    {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=windows-1256');
        }
        ini_set('default_charset', 'windows-1256');
    }
}
