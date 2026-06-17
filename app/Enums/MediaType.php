<?php

namespace App\Enums;

enum MediaType: string
{
    case Link = 'link';
    case File = 'file';
    case Image = 'image';
    case Video = 'video';

    public function label(): string
    {
        return match ($this) {
            self::Link => 'Link',
            self::File => 'File',
            self::Image => 'Image',
            self::Video => 'Video',
        };
    }

    /**
     * Whether this media type is stored on disk (vs. an external URL).
     */
    public function isUploaded(): bool
    {
        return $this !== self::Link;
    }
}
