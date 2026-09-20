<?php

namespace app\common\service\cms;

/**
 * Canonical persisted defaults for homepage section fields.
 */
class HomeSectionDefaults
{
    const SERVICE_BACKGROUND_IMAGE = '/uploads/cms-clone/pc/images/td_bg.jpg';
    const COMPANY_BACKGROUND_IMAGE = '/uploads/cms-clone/pc/images/abt_bg.jpg';
    const COMPANY_MOBILE_BACKGROUND_IMAGE = '/uploads/cms-clone/mobile/images/abt.jpg';

    /**
     * Canonical uploaded-source defaults for the homepage workshop block.
     *
     * @return array
     */
    public static function workshop()
    {
        $items = [
            [
                'title' => '高速高精度印刷机 适配大小定单',
                'text' => '',
                'image' => '/uploads/cms-home/workshop/workshop-01.jpg',
                'mobile_image' => '/uploads/cms-home/workshop/workshop-01.jpg',
                'url' => '',
                'pc_visible' => 1,
                'mobile_visible' => 1,
            ],
            [
                'title' => '引进进口柔印十色高速机',
                'text' => '',
                'image' => '/uploads/cms-home/workshop/workshop-02.jpg',
                'mobile_image' => '/uploads/cms-home/workshop/workshop-02.jpg',
                'url' => '',
                'pc_visible' => 1,
                'mobile_visible' => 1,
            ],
            [
                'title' => '自动化，高精度大小定单快捷工艺齐全',
                'text' => '',
                'image' => '/uploads/cms-home/workshop/workshop-03.jpg',
                'mobile_image' => '/uploads/cms-home/workshop/workshop-03.jpg',
                'url' => '',
                'pc_visible' => 1,
                'mobile_visible' => 1,
            ],
            [
                'title' => '一线品牌制袋机 快速稳定高效',
                'text' => '',
                'image' => '/uploads/cms-home/workshop/workshop-04.jpg',
                'mobile_image' => '/uploads/cms-home/workshop/workshop-04.jpg',
                'url' => '',
                'pc_visible' => 1,
                'mobile_visible' => 1,
            ],
            [
                'title' => '高速复合机制袋机 分切机',
                'text' => '',
                'image' => '/uploads/cms-home/workshop/workshop-05.jpg',
                'mobile_image' => '/uploads/cms-home/workshop/workshop-05.jpg',
                'url' => '',
                'pc_visible' => 1,
                'mobile_visible' => 1,
            ],
            [
                'title' => '个性化定制 品类多样',
                'text' => '',
                'image' => '/uploads/cms-home/workshop/workshop-06.jpg',
                'mobile_image' => '/uploads/cms-home/workshop/workshop-06.jpg',
                'url' => '',
                'pc_visible' => 1,
                'mobile_visible' => 1,
            ],
        ];

        return [
            'section_name' => '生产车间',
            'title' => '生产车间',
            'subtitle' => '一人一份责任，全员一份口碑  严谨务实，高效协作',
            'config' => ['items' => $items],
            'pc_visible' => 1,
            'mobile_visible' => 1,
            'weigh' => 550,
            'status' => 'normal',
        ];
    }

    /**
     * Canonical uploaded-source defaults for the homepage culture block.
     *
     * @return array
     */
    public static function culture()
    {
        return [
            'section_name' => '企业文化',
            'title' => '企业文化',
            'subtitle' => 'CORPORATE CULTURE',
            'background_image' => '/uploads/cms-home/culture/culture-background.jpg',
            'mobile_background_image' => '/uploads/cms-home/culture/culture-background.jpg',
            'config' => [
                'items' => [
                    [
                        'icon' => '/uploads/cms-home/culture/culture-vision.svg',
                        'title' => '企业愿景',
                        'text' => "深耕包装，创造持续价值\n设计创造精品\n包装传递价值",
                        'pc_visible' => 1,
                        'mobile_visible' => 1,
                    ],
                    [
                        'icon' => '/uploads/cms-home/culture/culture-mission.svg',
                        'title' => '企业使命',
                        'text' => "赋能客户，造福社会\n成为员工幸福、客户满意\n社会尊敬的幸福企业",
                        'pc_visible' => 1,
                        'mobile_visible' => 1,
                    ],
                    [
                        'icon' => '/uploads/cms-home/culture/culture-values.svg',
                        'title' => '核心价值观',
                        'text' => "诚信 品质 高效\n创新 共创 共赢",
                        'pc_visible' => 1,
                        'mobile_visible' => 1,
                    ],
                    [
                        'icon' => '/uploads/cms-home/culture/culture-spirit.svg',
                        'title' => '企业精神',
                        'text' => "以客户为中心\n脚踏实地 协作致远\n专注 务实 热情 严谨 感恩",
                        'pc_visible' => 1,
                        'mobile_visible' => 1,
                    ],
                ],
            ],
            'pc_visible' => 1,
            'mobile_visible' => 1,
            'weigh' => 0,
            'status' => 'normal',
        ];
    }

    /**
     * Apply defaults only to fields explicitly submitted by an editor.
     * Omitted fields stay omitted so patch/merge semantics remain intact.
     *
     * @param string $sectionKey
     * @param array  $fields
     * @return array
     */
    public static function applySubmitted($sectionKey, array $fields)
    {
        if ((string)$sectionKey === 'service'
            && array_key_exists('background_image', $fields)
            && trim((string)$fields['background_image']) === '') {
            $fields['background_image'] = self::SERVICE_BACKGROUND_IMAGE;
        }
        if ((string)$sectionKey === 'company') {
            if (array_key_exists('background_image', $fields)
                && trim((string)$fields['background_image']) === '') {
                $fields['background_image'] = self::COMPANY_BACKGROUND_IMAGE;
            }
            if (array_key_exists('mobile_background_image', $fields)
                && trim((string)$fields['mobile_background_image']) === '') {
                $fields['mobile_background_image'] = self::COMPANY_MOBILE_BACKGROUND_IMAGE;
            }
        }
        return $fields;
    }
}
