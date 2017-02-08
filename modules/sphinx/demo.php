<?php
define('SYSPATH', '..');
define('MODPATH', '..');
require_once MODPATH . '/sphinx/sphinx.php';

$config = array(
    'default' => array(
        'host' => "192.168.94.22",
        'port' => 8836,
        'index' => "xy_community_main",
        'options' => array(
            'before_match' => '<b style="color:red">',
            'after_match' => '</b>',
            'chunk_separator' => '...',
            'limit' => 60, //关键字上下文60个字
            'around' => 5 //每个关键词块左右选取的词的数目
        )
    )
);

//API参见 http://www.coreseek.cn/docs/coreseek_3.2-sphinx_0.9.9.html#sorting-modes
$sphinx = new Module_Sphinx($config['default']);
//$sphinx->getClient()->SetArrayResult(true);
$sphinx->getClient()->SetFilter('type', array(2));
$sphinx->getClient()->SetFilter('category', array(1001));
$sphinx->getClient()->SetSortMode(SPH_SORT_EXTENDED, 'heat DESC');
$result = $sphinx->getClient()->query("是", $config['default']['index']);

//var_dump(array_keys($result['matches']));exit;
if ($result) {
    foreach ($result as $key => $val) {
        echo "--------$key\n";
        print_r($val);
        echo "\n";
    }
}
//exit;


# BuildKeywords
$keywords = $sphinx->BuildKeywords("福建共青团省委短信网关");
print_r($keywords);
echo("<br/>\n");

# BuildExcerpts
$intro = <<<EOT
一、企业简介

网龙公司(NetDragon Websoft Inc.)，1999年成立于福州，是中国网络游戏和移动互联网应用的开发商和运营商的领导者之一，也是一家富于创新的设计型高科技公司。现今各大分部延伸至全球，跻身福布斯全球企业2000强、全国文化企业30强。2008年6月24日网龙在香港主板上市(主板股票代码：00777.HK)。

创新是网龙身为设计型公司稳健发展的源动力。初创时，网龙以创意和热情缔造了中国网游多项奇迹。《幻灵游侠》、《魔域》、《征服》等均成为玩家心目中的经典。网龙是首个开拓国际市场并成功运营的中国网游企业，已成为美国市场最大的中国网游运营商，覆盖英、法、西班牙、阿拉伯等多种语言区域，迈出了民族网游国际化的历史性步伐。此外，网龙还一手创建了中国网络游戏第一门户——17173，全球最大的第三方应用分发平台——91无线和安卓市场，风云一时无两。2013年8月，网龙以19亿美金出售91无线股权予百度，创下当时互联网史上最大的一次并购。

当前，定位设计型公司的网龙着力开拓教育市场。一群富有创意，对教育事业充满热情并勇于担当的网龙人投身在线教育领域，引领中国在线教育和终身学习产业健康发展。网龙已推出“非学历教育全国公服体系”和 “全国远程中职公共服务体系”，并组建K12教育事业部，为国家教育均等化、教育学习模式创新做出应有贡献;网龙还精心打造了“网龙云办公”系列产品，助推国家中小企业信息化建设;在文化创意产业方面，网龙规划设计并启动 “海西动漫创意之都”项目，一座融汇传统国学和IT科技于一身的海西最高端的动漫文化教育产业基地正振翅腾飞。网龙紧紧围绕科技教育事业这一核心与基点，从“教育产品起步、又制作传播教育产品、再回到用教育提升科技”，展现着对国家和社会的使命与担当。

设计引领未来。经过15年的发展，网龙积累了强大的科技创新能力和丰富的海外市场拓展经验，拥有国内顶尖的科技研发团队和一流的创新型企业文化。在这个平均年龄只有25岁的朝气蓬勃的团队里，任何奇迹都可以被创造，任何梦想都可以去实现!

Better ND Best U，你值得降落更好的星球，我们期待你的加入。

二、应聘流程

网申——在线测评——宣讲——笔试——面试——录用

三、简历投递

如果您是2015年应届毕业生，有志于在互联网行业不断发展，富有激情和创新意识，勇于接受新鲜的挑战，欢迎您通过以下任一渠道查看并投递网龙公司的意向职位。

前程无忧：campus.51job.com/ND

官方网站：campus.nd.com.cn

微信直投：@网龙校招

网龙公司2015校园招聘现已启动，在线申请时间为9月12日-10月23日。

欢迎同学们踊跃报名! (注：每位同学投递一份简历即可，并请填齐所有必填内容，以保证顺利投递简历。在投递过程中，如您有任何疑问，可直接与官方微博 @网龙校招 沟通，我们会尽快为您解答。)

四、招聘需求

岗位类别 专业要求 招聘人数

策 划 类 不限(计算机相关专业优先) 65

开 发 类 计算机相关专业 100

MT 类 专业不限 20

营 销 类 新闻传播/广告学/市场营销相关专业 若干

美 术 类 美术相关专业 若干

技 术 支 持 类 计算机相关专业 若干

(详细岗位信息请登录前程无忧、公司校招官网或关注官方微信@网龙校招了解)
EOT;

$docs = array(
    "title" => "网龙公司2014校园招聘",
    "details" => $intro,
    "address" => "地 点： 国际交流公司中心一楼多功能报告厅",
);
$result = $sphinx->BuildExcerpts($docs, '公司');


var_dump($result);
