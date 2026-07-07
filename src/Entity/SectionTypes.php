<?php

namespace App\Entity;

final class SectionTypes
{
    public const HEADER = 'header';
    public const HERO = 'hero';
    public const BODY = 'body';
    public const IMAGE = 'image';
    public const CARDS = 'cards';
    public const CARDS_PREMIUM = 'cards_premium';
    public const FAQ = 'faq';
    public const FORM = 'form';
    public const CTA = 'cta';
    public const FOOTER = 'footer';

    public const ALL = [
        self::HEADER,
        self::HERO,
        self::BODY,
        self::IMAGE,
        self::CARDS,
        self::CARDS_PREMIUM,
        self::FAQ,
        self::FORM,
        self::CTA,
        self::FOOTER,
    ];
}
