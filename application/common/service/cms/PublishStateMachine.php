<?php

namespace app\common\service\cms;

/**
 * CMS 内容发布状态机。
 */
class PublishStateMachine
{
    const DRAFT = 'draft';
    const PENDING = 'pending';
    const PUBLISHED = 'published';
    const REJECTED = 'rejected';
    const OFFLINE = 'offline';
    const SCHEDULED = 'scheduled';

    protected static $transitions = [
        self::DRAFT => [self::PENDING],
        self::PENDING => [self::PUBLISHED, self::REJECTED, self::SCHEDULED],
        self::REJECTED => [self::DRAFT, self::PENDING],
        self::PUBLISHED => [self::OFFLINE, self::PENDING],
        self::OFFLINE => [self::PENDING, self::PUBLISHED],
        self::SCHEDULED => [self::PUBLISHED, self::DRAFT, self::OFFLINE],
    ];

    public static function canTransition($from, $to)
    {
        return isset(self::$transitions[$from]) && in_array($to, self::$transitions[$from], true);
    }

    public static function assertTransition($from, $to)
    {
        if (!self::canTransition($from, $to)) {
            throw new \InvalidArgumentException(sprintf('不允许从 %s 变更为 %s', $from, $to));
        }
    }

    public static function labels()
    {
        return [
            self::DRAFT => '草稿',
            self::PENDING => '待审核',
            self::PUBLISHED => '已发布',
            self::REJECTED => '已驳回',
            self::OFFLINE => '已下架',
            self::SCHEDULED => '定时发布',
        ];
    }
}
