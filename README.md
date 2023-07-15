# XDU-Planet
Modified version of littleclie code, with api and atom feed support.

## 这个项目的由来
事情从我看同学博客说起。我感觉把我同学的博客都聚合到一起看，看起来更方便，而且还能满足我一直想当“干新闻的”想法。毕竟，我在校内开过一段时间电台，天天报新闻()

我的想法是，按照时间更新 RSS 数据，然后对其进行解码渲染。客户端先选择对象，然后选择文章，就可以看了。我的想法可以说是服务器版本的 RSS 阅读器，只不过 RSS 列表是我写好的同学博客。这个和看报纸十分相似：作者给报社供稿，报社展示。所以我说有点像“搞新闻的”。

### 第一次尝试
大约是去年，有个人写了个 go 语言实现的 xduplanet，他的想法是运行一个服务器程序，内部订好抓取 RSS 源的顺序，然后渲染显示。我当下来代码发现，虽然他能做到我上面的想法，但是开发阶段比较原始，他只有一个 json 文件写入所有人的 RSS 链接。而且页面是一个文章列表，而不是先选人再选文章。当时我时间较紧，能力也没那么强，我就作罢。

### 第二次尝试
在做 Web 工程大作业的时候，我们组不知为啥，搞的是从网上获取数据，然后用 RSS 输出数据的软件。他是一个管道过滤器结构，RSS 输出只是一个最终的端口。这也是我的一大意向程序，然而我并没帮忙开发多少，只是验收的时候我去演示了而已:-P

这个软件实际上十分接近我的需求了，我可以按照列表，推送当天同学博客有啥更新。但是那次演示完了，我感觉我是不想用了……

### 这次尝试
前几天有个玩老电脑的推荐我 68k.news 和小可怜网的代码。虽然一开始我只是抱着看看能不能用，在服务器上运行了一下，但是我看这网站，感觉我的 XDU Planet 想法终于可以实现了，而且这回基本算是做熟了，摆在了我的桌子上。但是你吃饭还得把菜放在嘴里.....

### 代码修改

修改了两个方面：Atom 源的支持和 API

#### Atom 源头
这个项目，由于本身是为了不支持 js 的老电脑显示当前新闻，他使用了 php-cgi 。不过没关心，我看代码十分简单，我就直接上了。我先简单地安排了我同学的博客，然后发现很多人的博客都显示不完全。

我去查看了这个项目使用的 php rss 库下面两个示例文件，和本项目进行比较。我发现本项目只支持 RSS 源头，而且 RSS 只支持查看 description 的东西。诚然对于显示新闻的 RSS 源来说，是够用了，但是我同学博客使用了不同的框架，这些框架输出源的格式对于这个程序，算超刚了:-P

 - 一类是输出了 Atom 类型的源，这个打开头就和 RSS 不一样，程序最后报错。
 - 一类是 RSS 源，但是内容都在 content:encoded 里面，这就是显示不完整。

我是一个基本没接触过 PHP 的人，但是代码比较好理解，我发动 CV 大法，把示例文件里面的解码文件抄了过来。然后就可以显示了……乱码。

没错，可能是为了兼容老电脑，这个东西使用国标码编码中文。我还得套一层中文编码器，这个我倒感觉能理解。毕竟这个网站本身是针对 Windows 98 级别的老爷机了。

以上修改都是针对 channel.php 文件，也就是输出 RSS 文章列表和文章内容的代码。这个项目有很多诸如 channel_ppc.php 等文件，我故意没动，让他们管理我新闻东西的输出，看着新闻这点阵字体，真的很怀旧。

#### API
上面的修改过程也是对本项目架构和功能的一个了解。在此之后，我魔改生成 Json 响应代码也就顺水成舟了。

这就又要扯到 Traintime PDA 了，这是我大半年以来一直在写的信息查看器。我一直想给这玩意扩充点好玩的功能。最一开始我把很久以前给别人录的饭堂和综合楼数据，在我的程序上进行渲染。但是我总想给这玩意扩充点类似“社交”的功能。正好 XDU Planet 可以在一定程度上满足这个需求，这个可以给大家展示我们同学公开的实用东西，而且这个东西也不需要获取啥机密讯息：他们的博客都是在互联网上公开的，而且终端只需要单方面获取就行了，这是一个单向的信息传递。我的代码也都是开放的，十分符合我开源的执念233

前面我提到了 channel.php 的用途，而这个代码的前半部分基本不需要修改，我只需要把下面的 PHP-HTML 混合代码修改成纯脚本输出就行了。PHP 本身支持设置返回头啥的，我只需要把返回头设置成 application/json，然后使用 exit 函数返回我的数据就行了。这就是初步的修改。

显然这么简单一改是无法完全我的需求的，API 面向的客户端可不是老年机。为了最后的 Eye candy，我对最核心的数据结构进行了修改。你们可以查看我的 xdurepo.php 文件，我把这个人的图标，名称，地址都传了过去。而最终为了输出 json 更好被客户端解码，我在每个返回值外面都套上了 stdClass。

基本上 API 就算这样了，我这里写出来。

```
GET /xduplanet.php

{
  "repos": {
    "benderblog": {         // key: 该对象名称，后面查询使用
      "name": "SuperBart/Benderblog ~ 开发者",     // 显示名称
      "website": "https://www.superbart.xyz",     // 博客网站
      "feed": "https://www.superbart.xyz/index.xml",    // RSS 或 ATOM 源头
      "favicon": "https://legacy.superbart.xyz/favicon.ico" // 图标
    },
  }
}
```

```
GET /xduplanet.php?feed=key
其中 key 是上述提到的后面查询使用的 key

{
  // 文章列表，下面分别对应标题，发布时间和来源链接
  "list": [
    {
      "title": "Flutter 介绍",
      "time": "2023-04-29T08:00:00+08:00",
      "url": "https://www.superbart.xyz/p/flutter-introduction.html"
    },
  ],
  // lastUpdateDate：本数据的获取时间，为将来备
  "lastUpdateTime": 1689432413用
}
```

```
GET /xduplanet.php?feed=key&p=0
其中 key 是上述提到的后面查询使用的 key，p 指上面文章列表中第几个文章

// 文章标题，来源链接，发布时间，内容
// 注意内容是和前面所述老网站网页输出是一样的，也就是图片，视频等会被隐藏掉
{
  "title": "Flutter 介绍",
  "link": "https://www.superbart.xyz/p/flutter-introduction.html",
  "time": 1682726400,
  "content": "Omit here."
}
```
我在 Traintime PDA 里面的相关分页也写了相关页面，还明白了 PageView 如何保存状态，每天一个编程小常识这就是？

### 后面的胡说阶段
我把这个服务挂在了我的服务器上，还上新了一些外网著名媒体。没敢上那些明显危险的，我感觉这些媒体都算是比较中立的吧:-P

对于新闻，作为一个看了十年中央十三台的人来说，我有点无法理解拿新闻看乐子的人的想法。虽然这个方式确实是给了我这个老木头一点亮光。比如说，前几天是故日本首相安倍晋三被刺杀一周年纪念日，我作为严肃看新闻的，去看现任首相岸田文雄去纪念，然后继续不顾周边国家骂街去放核污水。但是有人说“日本JK被摔倒一周年纪念日”，我总觉得有点无语，倒也觉得正常。

但我发现，有些时候真的有点太过了。比如说俄乌战争，本来我去网上只是去看战争状况，然后就是无论从那个角度，都是“俄罗斯粉丝”和“乌克兰粉丝”互相隔空输出，顺便拿乌克兰找乐啥的。我总觉得拿人被欺负开玩笑很不合适，虽然这件事本身很复杂，没有任何人是无辜的。虽然我早就接触过楼下老大爷天天建政，也算“身经百战”，但是我感觉，这可是一个了解世界，了解想法很重要的渠道，就这么当作乐子，真的有点不太合适。

我希望看到的是对一个事件有调查，有分析，有结论的东西，这样无论如何至少都能骂两句(不是)。而不是“今天澳大利亚又刮起了沙尘暴，养羊的少了几只羊，估计是走失了”这样的短视频。

## 原来介绍 littleclie
A RSS to simple HTML converter designed for webclip

可怜网（clie.com.cn）是原Hi-PDA网友“番茄”（jimeinstein）搭建的适合于掌上电脑、PDA浏览的网站，clie意为“Chinese Lite Info Express”（中文轻资讯快报），亦与索尼的PDA品牌Clié相仿。 Hi-PDA上关于可怜网的帖子 发表于2003年10月22日。

网页里的电脑博物馆搭建的“小可怜网”模仿了可怜网的新闻订阅功能，它可以通过Pocket IE（Pocket PC/Windows CE/Windows Mobile）、Xiino（Palm OS）等旧式移动浏览器访问，也支持通过HandStory、iSilo等离线移动阅读工具订阅。

Clie.com.cn is a website built by the original Hi-PDA user "Tomato" (jimeinstein) for use on handheld computers and PDAs. Clie stands for "Chinese Lite Info Express" and is similar to Sony's PDA brand "Clié". A post about Clie.com.cn was published on Hi-PDA on October 22, 2003.

The "Little Clie" imitates Clie.com.cn's news subscription function and can be accessed through old-style mobile browsers such as Pocket IE (Pocket PC/Windows CE/Windows Mobile) and Xiino (Palm OS), and can also be subscribed through offline mobile reading tools such as HandStory and iSilo.