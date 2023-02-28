<?php

namespace EasySub\Tools;

class Country
{
    protected static string $countryCode = '[
    {
        "id": 4,
        "alpha2": "af",
        "alpha3": "afg",
        "name": "阿富汗"
    },
    {
        "id": 248,
        "alpha2": "ax",
        "alpha3": "ala",
        "name": "奥兰"
    },
    {
        "id": 8,
        "alpha2": "al",
        "alpha3": "alb",
        "name": "阿尔巴尼亚"
    },
    {
        "id": 12,
        "alpha2": "dz",
        "alpha3": "dza",
        "name": "阿尔及利亚"
    },
    {
        "id": 16,
        "alpha2": "as",
        "alpha3": "asm",
        "name": "美属萨摩亚"
    },
    {
        "id": 20,
        "alpha2": "ad",
        "alpha3": "and",
        "name": "安道尔"
    },
    {
        "id": 24,
        "alpha2": "ao",
        "alpha3": "ago",
        "name": "安哥拉"
    },
    {
        "id": 660,
        "alpha2": "ai",
        "alpha3": "aia",
        "name": "安圭拉"
    },
    {
        "id": 10,
        "alpha2": "aq",
        "alpha3": "ata",
        "name": "南极洲"
    },
    {
        "id": 28,
        "alpha2": "ag",
        "alpha3": "atg",
        "name": "安提瓜和巴布达"
    },
    {
        "id": 32,
        "alpha2": "ar",
        "alpha3": "arg",
        "name": "阿根廷"
    },
    {
        "id": 51,
        "alpha2": "am",
        "alpha3": "arm",
        "name": "亚美尼亚"
    },
    {
        "id": 533,
        "alpha2": "aw",
        "alpha3": "abw",
        "name": "阿鲁巴"
    },
    {
        "id": 36,
        "alpha2": "au",
        "alpha3": "aus",
        "name": "澳大利亚"
    },
    {
        "id": 40,
        "alpha2": "at",
        "alpha3": "aut",
        "name": "奥地利"
    },
    {
        "id": 31,
        "alpha2": "az",
        "alpha3": "aze",
        "name": "阿塞拜疆"
    },
    {
        "id": 44,
        "alpha2": "bs",
        "alpha3": "bhs",
        "name": "巴哈马"
    },
    {
        "id": 48,
        "alpha2": "bh",
        "alpha3": "bhr",
        "name": "巴林"
    },
    {
        "id": 50,
        "alpha2": "bd",
        "alpha3": "bgd",
        "name": "孟加拉国"
    },
    {
        "id": 52,
        "alpha2": "bb",
        "alpha3": "brb",
        "name": "巴巴多斯"
    },
    {
        "id": 112,
        "alpha2": "by",
        "alpha3": "blr",
        "name": "白俄罗斯"
    },
    {
        "id": 56,
        "alpha2": "be",
        "alpha3": "bel",
        "name": "比利时"
    },
    {
        "id": 84,
        "alpha2": "bz",
        "alpha3": "blz",
        "name": "伯利兹"
    },
    {
        "id": 204,
        "alpha2": "bj",
        "alpha3": "ben",
        "name": "贝宁"
    },
    {
        "id": 60,
        "alpha2": "bm",
        "alpha3": "bmu",
        "name": "百慕大"
    },
    {
        "id": 64,
        "alpha2": "bt",
        "alpha3": "btn",
        "name": "不丹"
    },
    {
        "id": 68,
        "alpha2": "bo",
        "alpha3": "bol",
        "name": "玻利维亚"
    },
    {
        "id": 535,
        "alpha2": "bq",
        "alpha3": "bes",
        "name": "荷兰加勒比区"
    },
    {
        "id": 70,
        "alpha2": "ba",
        "alpha3": "bih",
        "name": "波黑"
    },
    {
        "id": 72,
        "alpha2": "bw",
        "alpha3": "bwa",
        "name": "博茨瓦纳"
    },
    {
        "id": 74,
        "alpha2": "bv",
        "alpha3": "bvt",
        "name": "布韦岛"
    },
    {
        "id": 76,
        "alpha2": "br",
        "alpha3": "bra",
        "name": "巴西"
    },
    {
        "id": 86,
        "alpha2": "io",
        "alpha3": "iot",
        "name": "英属印度洋领地"
    },
    {
        "id": 96,
        "alpha2": "bn",
        "alpha3": "brn",
        "name": "文莱"
    },
    {
        "id": 100,
        "alpha2": "bg",
        "alpha3": "bgr",
        "name": "保加利亚"
    },
    {
        "id": 854,
        "alpha2": "bf",
        "alpha3": "bfa",
        "name": "布基纳法索"
    },
    {
        "id": 108,
        "alpha2": "bi",
        "alpha3": "bdi",
        "name": "布隆迪"
    },
    {
        "id": 132,
        "alpha2": "cv",
        "alpha3": "cpv",
        "name": "佛得角"
    },
    {
        "id": 116,
        "alpha2": "kh",
        "alpha3": "khm",
        "name": "柬埔寨"
    },
    {
        "id": 120,
        "alpha2": "cm",
        "alpha3": "cmr",
        "name": "喀麦隆"
    },
    {
        "id": 124,
        "alpha2": "ca",
        "alpha3": "can",
        "name": "加拿大"
    },
    {
        "id": 136,
        "alpha2": "ky",
        "alpha3": "cym",
        "name": "开曼群岛"
    },
    {
        "id": 140,
        "alpha2": "cf",
        "alpha3": "caf",
        "name": "中非"
    },
    {
        "id": 148,
        "alpha2": "td",
        "alpha3": "tcd",
        "name": "乍得"
    },
    {
        "id": 152,
        "alpha2": "cl",
        "alpha3": "chl",
        "name": "智利"
    },
    {
        "id": 156,
        "alpha2": "cn",
        "alpha3": "chn",
        "name": "中国"
    },
    {
        "id": 162,
        "alpha2": "cx",
        "alpha3": "cxr",
        "name": "圣诞岛"
    },
    {
        "id": 166,
        "alpha2": "cc",
        "alpha3": "cck",
        "name": "科科斯（基林）群岛"
    },
    {
        "id": 170,
        "alpha2": "co",
        "alpha3": "col",
        "name": "哥伦比亚"
    },
    {
        "id": 174,
        "alpha2": "km",
        "alpha3": "com",
        "name": "科摩罗"
    },
    {
        "id": 178,
        "alpha2": "cg",
        "alpha3": "cog",
        "name": "刚果共和国"
    },
    {
        "id": 180,
        "alpha2": "cd",
        "alpha3": "cod",
        "name": "刚果民主共和国"
    },
    {
        "id": 184,
        "alpha2": "ck",
        "alpha3": "cok",
        "name": "库克群岛"
    },
    {
        "id": 188,
        "alpha2": "cr",
        "alpha3": "cri",
        "name": "哥斯达黎加"
    },
    {
        "id": 384,
        "alpha2": "ci",
        "alpha3": "civ",
        "name": "科特迪瓦"
    },
    {
        "id": 191,
        "alpha2": "hr",
        "alpha3": "hrv",
        "name": "克罗地亚"
    },
    {
        "id": 192,
        "alpha2": "cu",
        "alpha3": "cub",
        "name": "古巴"
    },
    {
        "id": 531,
        "alpha2": "cw",
        "alpha3": "cuw",
        "name": "库拉索"
    },
    {
        "id": 196,
        "alpha2": "cy",
        "alpha3": "cyp",
        "name": "塞浦路斯"
    },
    {
        "id": 203,
        "alpha2": "cz",
        "alpha3": "cze",
        "name": "捷克"
    },
    {
        "id": 208,
        "alpha2": "dk",
        "alpha3": "dnk",
        "name": "丹麦"
    },
    {
        "id": 262,
        "alpha2": "dj",
        "alpha3": "dji",
        "name": "吉布提"
    },
    {
        "id": 212,
        "alpha2": "dm",
        "alpha3": "dma",
        "name": "多米尼克"
    },
    {
        "id": 214,
        "alpha2": "do",
        "alpha3": "dom",
        "name": "多米尼加"
    },
    {
        "id": 218,
        "alpha2": "ec",
        "alpha3": "ecu",
        "name": "厄瓜多尔"
    },
    {
        "id": 818,
        "alpha2": "eg",
        "alpha3": "egy",
        "name": "埃及"
    },
    {
        "id": 222,
        "alpha2": "sv",
        "alpha3": "slv",
        "name": "萨尔瓦多"
    },
    {
        "id": 226,
        "alpha2": "gq",
        "alpha3": "gnq",
        "name": "赤道几内亚"
    },
    {
        "id": 232,
        "alpha2": "er",
        "alpha3": "eri",
        "name": "厄立特里亚"
    },
    {
        "id": 233,
        "alpha2": "ee",
        "alpha3": "est",
        "name": "爱沙尼亚"
    },
    {
        "id": 748,
        "alpha2": "sz",
        "alpha3": "swz",
        "name": "斯威士兰"
    },
    {
        "id": 231,
        "alpha2": "et",
        "alpha3": "eth",
        "name": "埃塞俄比亚"
    },
    {
        "id": 238,
        "alpha2": "fk",
        "alpha3": "flk",
        "name": "福克兰群岛"
    },
    {
        "id": 234,
        "alpha2": "fo",
        "alpha3": "fro",
        "name": "法罗群岛"
    },
    {
        "id": 242,
        "alpha2": "fj",
        "alpha3": "fji",
        "name": "斐济"
    },
    {
        "id": 246,
        "alpha2": "fi",
        "alpha3": "fin",
        "name": "芬兰"
    },
    {
        "id": 250,
        "alpha2": "fr",
        "alpha3": "fra",
        "name": "法国"
    },
    {
        "id": 254,
        "alpha2": "gf",
        "alpha3": "guf",
        "name": "法属圭亚那"
    },
    {
        "id": 258,
        "alpha2": "pf",
        "alpha3": "pyf",
        "name": "法属波利尼西亚"
    },
    {
        "id": 260,
        "alpha2": "tf",
        "alpha3": "atf",
        "name": "法属南部和南极领地"
    },
    {
        "id": 266,
        "alpha2": "ga",
        "alpha3": "gab",
        "name": "加蓬"
    },
    {
        "id": 270,
        "alpha2": "gm",
        "alpha3": "gmb",
        "name": "冈比亚"
    },
    {
        "id": 268,
        "alpha2": "ge",
        "alpha3": "geo",
        "name": "格鲁吉亚"
    },
    {
        "id": 276,
        "alpha2": "de",
        "alpha3": "deu",
        "name": "德国"
    },
    {
        "id": 288,
        "alpha2": "gh",
        "alpha3": "gha",
        "name": "加纳"
    },
    {
        "id": 292,
        "alpha2": "gi",
        "alpha3": "gib",
        "name": "直布罗陀"
    },
    {
        "id": 300,
        "alpha2": "gr",
        "alpha3": "grc",
        "name": "希腊"
    },
    {
        "id": 304,
        "alpha2": "gl",
        "alpha3": "grl",
        "name": "格陵兰"
    },
    {
        "id": 308,
        "alpha2": "gd",
        "alpha3": "grd",
        "name": "格林纳达"
    },
    {
        "id": 312,
        "alpha2": "gp",
        "alpha3": "glp",
        "name": "瓜德罗普"
    },
    {
        "id": 316,
        "alpha2": "gu",
        "alpha3": "gum",
        "name": "关岛"
    },
    {
        "id": 320,
        "alpha2": "gt",
        "alpha3": "gtm",
        "name": "危地马拉"
    },
    {
        "id": 831,
        "alpha2": "gg",
        "alpha3": "ggy",
        "name": "根西"
    },
    {
        "id": 324,
        "alpha2": "gn",
        "alpha3": "gin",
        "name": "几内亚"
    },
    {
        "id": 624,
        "alpha2": "gw",
        "alpha3": "gnb",
        "name": "几内亚比绍"
    },
    {
        "id": 328,
        "alpha2": "gy",
        "alpha3": "guy",
        "name": "圭亚那"
    },
    {
        "id": 332,
        "alpha2": "ht",
        "alpha3": "hti",
        "name": "海地"
    },
    {
        "id": 334,
        "alpha2": "hm",
        "alpha3": "hmd",
        "name": "赫德岛和麦克唐纳群岛"
    },
    {
        "id": 336,
        "alpha2": "va",
        "alpha3": "vat",
        "name": "梵蒂冈"
    },
    {
        "id": 340,
        "alpha2": "hn",
        "alpha3": "hnd",
        "name": "洪都拉斯"
    },
    {
        "id": 344,
        "alpha2": "hk",
        "alpha3": "hkg",
        "name": "香港"
    },
    {
        "id": 348,
        "alpha2": "hu",
        "alpha3": "hun",
        "name": "匈牙利"
    },
    {
        "id": 352,
        "alpha2": "is",
        "alpha3": "isl",
        "name": "冰岛"
    },
    {
        "id": 356,
        "alpha2": "in",
        "alpha3": "ind",
        "name": "印度"
    },
    {
        "id": 360,
        "alpha2": "id",
        "alpha3": "idn",
        "name": "印度尼西亚"
    },
    {
        "id": 364,
        "alpha2": "ir",
        "alpha3": "irn",
        "name": "伊朗"
    },
    {
        "id": 368,
        "alpha2": "iq",
        "alpha3": "irq",
        "name": "伊拉克"
    },
    {
        "id": 372,
        "alpha2": "ie",
        "alpha3": "irl",
        "name": "爱尔兰"
    },
    {
        "id": 833,
        "alpha2": "im",
        "alpha3": "imn",
        "name": "马恩岛"
    },
    {
        "id": 376,
        "alpha2": "il",
        "alpha3": "isr",
        "name": "以色列"
    },
    {
        "id": 380,
        "alpha2": "it",
        "alpha3": "ita",
        "name": "意大利"
    },
    {
        "id": 388,
        "alpha2": "jm",
        "alpha3": "jam",
        "name": "牙买加"
    },
    {
        "id": 392,
        "alpha2": "jp",
        "alpha3": "jpn",
        "name": "日本"
    },
    {
        "id": 832,
        "alpha2": "je",
        "alpha3": "jey",
        "name": "泽西"
    },
    {
        "id": 400,
        "alpha2": "jo",
        "alpha3": "jor",
        "name": "约旦"
    },
    {
        "id": 398,
        "alpha2": "kz",
        "alpha3": "kaz",
        "name": "哈萨克斯坦"
    },
    {
        "id": 404,
        "alpha2": "ke",
        "alpha3": "ken",
        "name": "肯尼亚"
    },
    {
        "id": 296,
        "alpha2": "ki",
        "alpha3": "kir",
        "name": "基里巴斯"
    },
    {
        "id": 408,
        "alpha2": "kp",
        "alpha3": "prk",
        "name": "朝鲜"
    },
    {
        "id": 410,
        "alpha2": "kr",
        "alpha3": "kor",
        "name": "韩国"
    },
    {
        "id": 414,
        "alpha2": "kw",
        "alpha3": "kwt",
        "name": "科威特"
    },
    {
        "id": 417,
        "alpha2": "kg",
        "alpha3": "kgz",
        "name": "吉尔吉斯斯坦"
    },
    {
        "id": 418,
        "alpha2": "la",
        "alpha3": "lao",
        "name": "老挝"
    },
    {
        "id": 428,
        "alpha2": "lv",
        "alpha3": "lva",
        "name": "拉脱维亚"
    },
    {
        "id": 422,
        "alpha2": "lb",
        "alpha3": "lbn",
        "name": "黎巴嫩"
    },
    {
        "id": 426,
        "alpha2": "ls",
        "alpha3": "lso",
        "name": "莱索托"
    },
    {
        "id": 430,
        "alpha2": "lr",
        "alpha3": "lbr",
        "name": "利比里亚"
    },
    {
        "id": 434,
        "alpha2": "ly",
        "alpha3": "lby",
        "name": "利比亚"
    },
    {
        "id": 438,
        "alpha2": "li",
        "alpha3": "lie",
        "name": "列支敦士登"
    },
    {
        "id": 440,
        "alpha2": "lt",
        "alpha3": "ltu",
        "name": "立陶宛"
    },
    {
        "id": 442,
        "alpha2": "lu",
        "alpha3": "lux",
        "name": "卢森堡"
    },
    {
        "id": 446,
        "alpha2": "mo",
        "alpha3": "mac",
        "name": "澳门"
    },
    {
        "id": 450,
        "alpha2": "mg",
        "alpha3": "mdg",
        "name": "马达加斯加"
    },
    {
        "id": 454,
        "alpha2": "mw",
        "alpha3": "mwi",
        "name": "马拉维"
    },
    {
        "id": 458,
        "alpha2": "my",
        "alpha3": "mys",
        "name": "马来西亚"
    },
    {
        "id": 462,
        "alpha2": "mv",
        "alpha3": "mdv",
        "name": "马尔代夫"
    },
    {
        "id": 466,
        "alpha2": "ml",
        "alpha3": "mli",
        "name": "马里"
    },
    {
        "id": 470,
        "alpha2": "mt",
        "alpha3": "mlt",
        "name": "马耳他"
    },
    {
        "id": 584,
        "alpha2": "mh",
        "alpha3": "mhl",
        "name": "马绍尔群岛"
    },
    {
        "id": 474,
        "alpha2": "mq",
        "alpha3": "mtq",
        "name": "马提尼克"
    },
    {
        "id": 478,
        "alpha2": "mr",
        "alpha3": "mrt",
        "name": "毛里塔尼亚"
    },
    {
        "id": 480,
        "alpha2": "mu",
        "alpha3": "mus",
        "name": "毛里求斯"
    },
    {
        "id": 175,
        "alpha2": "yt",
        "alpha3": "myt",
        "name": "马约特"
    },
    {
        "id": 484,
        "alpha2": "mx",
        "alpha3": "mex",
        "name": "墨西哥"
    },
    {
        "id": 583,
        "alpha2": "fm",
        "alpha3": "fsm",
        "name": "密克罗尼西亚联邦"
    },
    {
        "id": 498,
        "alpha2": "md",
        "alpha3": "mda",
        "name": "摩尔多瓦"
    },
    {
        "id": 492,
        "alpha2": "mc",
        "alpha3": "mco",
        "name": "摩纳哥"
    },
    {
        "id": 496,
        "alpha2": "mn",
        "alpha3": "mng",
        "name": "蒙古"
    },
    {
        "id": 499,
        "alpha2": "me",
        "alpha3": "mne",
        "name": "黑山"
    },
    {
        "id": 500,
        "alpha2": "ms",
        "alpha3": "msr",
        "name": "蒙特塞拉特"
    },
    {
        "id": 504,
        "alpha2": "ma",
        "alpha3": "mar",
        "name": "摩洛哥"
    },
    {
        "id": 508,
        "alpha2": "mz",
        "alpha3": "moz",
        "name": "莫桑比克"
    },
    {
        "id": 104,
        "alpha2": "mm",
        "alpha3": "mmr",
        "name": "缅甸"
    },
    {
        "id": 516,
        "alpha2": "na",
        "alpha3": "nam",
        "name": "纳米比亚"
    },
    {
        "id": 520,
        "alpha2": "nr",
        "alpha3": "nru",
        "name": "瑙鲁"
    },
    {
        "id": 524,
        "alpha2": "np",
        "alpha3": "npl",
        "name": "尼泊尔"
    },
    {
        "id": 528,
        "alpha2": "nl",
        "alpha3": "nld",
        "name": "荷兰"
    },
    {
        "id": 540,
        "alpha2": "nc",
        "alpha3": "ncl",
        "name": "新喀里多尼亚"
    },
    {
        "id": 554,
        "alpha2": "nz",
        "alpha3": "nzl",
        "name": "新西兰"
    },
    {
        "id": 558,
        "alpha2": "ni",
        "alpha3": "nic",
        "name": "尼加拉瓜"
    },
    {
        "id": 562,
        "alpha2": "ne",
        "alpha3": "ner",
        "name": "尼日尔"
    },
    {
        "id": 566,
        "alpha2": "ng",
        "alpha3": "nga",
        "name": "尼日利亚"
    },
    {
        "id": 570,
        "alpha2": "nu",
        "alpha3": "niu",
        "name": "纽埃"
    },
    {
        "id": 574,
        "alpha2": "nf",
        "alpha3": "nfk",
        "name": "诺福克岛"
    },
    {
        "id": 807,
        "alpha2": "mk",
        "alpha3": "mkd",
        "name": "北马其顿"
    },
    {
        "id": 580,
        "alpha2": "mp",
        "alpha3": "mnp",
        "name": "北马里亚纳群岛"
    },
    {
        "id": 578,
        "alpha2": "no",
        "alpha3": "nor",
        "name": "挪威"
    },
    {
        "id": 512,
        "alpha2": "om",
        "alpha3": "omn",
        "name": "阿曼"
    },
    {
        "id": 586,
        "alpha2": "pk",
        "alpha3": "pak",
        "name": "巴基斯坦"
    },
    {
        "id": 585,
        "alpha2": "pw",
        "alpha3": "plw",
        "name": "帕劳"
    },
    {
        "id": 275,
        "alpha2": "ps",
        "alpha3": "pse",
        "name": "巴勒斯坦"
    },
    {
        "id": 591,
        "alpha2": "pa",
        "alpha3": "pan",
        "name": "巴拿马"
    },
    {
        "id": 598,
        "alpha2": "pg",
        "alpha3": "png",
        "name": "巴布亚新几内亚"
    },
    {
        "id": 600,
        "alpha2": "py",
        "alpha3": "pry",
        "name": "巴拉圭"
    },
    {
        "id": 604,
        "alpha2": "pe",
        "alpha3": "per",
        "name": "秘鲁"
    },
    {
        "id": 608,
        "alpha2": "ph",
        "alpha3": "phl",
        "name": "菲律宾"
    },
    {
        "id": 612,
        "alpha2": "pn",
        "alpha3": "pcn",
        "name": "皮特凯恩群岛"
    },
    {
        "id": 616,
        "alpha2": "pl",
        "alpha3": "pol",
        "name": "波兰"
    },
    {
        "id": 620,
        "alpha2": "pt",
        "alpha3": "prt",
        "name": "葡萄牙"
    },
    {
        "id": 630,
        "alpha2": "pr",
        "alpha3": "pri",
        "name": "波多黎各"
    },
    {
        "id": 634,
        "alpha2": "qa",
        "alpha3": "qat",
        "name": "卡塔尔"
    },
    {
        "id": 638,
        "alpha2": "re",
        "alpha3": "reu",
        "name": "留尼汪"
    },
    {
        "id": 642,
        "alpha2": "ro",
        "alpha3": "rou",
        "name": "罗马尼亚"
    },
    {
        "id": 643,
        "alpha2": "ru",
        "alpha3": "rus",
        "name": "俄罗斯"
    },
    {
        "id": 646,
        "alpha2": "rw",
        "alpha3": "rwa",
        "name": "卢旺达"
    },
    {
        "id": 652,
        "alpha2": "bl",
        "alpha3": "blm",
        "name": "圣巴泰勒米"
    },
    {
        "id": 654,
        "alpha2": "sh",
        "alpha3": "shn",
        "name": "圣赫勒拿、阿森松和特里斯坦-达库尼亚"
    },
    {
        "id": 659,
        "alpha2": "kn",
        "alpha3": "kna",
        "name": "圣基茨和尼维斯"
    },
    {
        "id": 662,
        "alpha2": "lc",
        "alpha3": "lca",
        "name": "圣卢西亚"
    },
    {
        "id": 663,
        "alpha2": "mf",
        "alpha3": "maf",
        "name": "法属圣马丁"
    },
    {
        "id": 666,
        "alpha2": "pm",
        "alpha3": "spm",
        "name": "圣皮埃尔和密克隆"
    },
    {
        "id": 670,
        "alpha2": "vc",
        "alpha3": "vct",
        "name": "圣文森特和格林纳丁斯"
    },
    {
        "id": 882,
        "alpha2": "ws",
        "alpha3": "wsm",
        "name": "萨摩亚"
    },
    {
        "id": 674,
        "alpha2": "sm",
        "alpha3": "smr",
        "name": "圣马力诺"
    },
    {
        "id": 678,
        "alpha2": "st",
        "alpha3": "stp",
        "name": "圣多美和普林西比"
    },
    {
        "id": 682,
        "alpha2": "sa",
        "alpha3": "sau",
        "name": "沙特阿拉伯"
    },
    {
        "id": 686,
        "alpha2": "sn",
        "alpha3": "sen",
        "name": "塞内加尔"
    },
    {
        "id": 688,
        "alpha2": "rs",
        "alpha3": "srb",
        "name": "塞尔维亚"
    },
    {
        "id": 690,
        "alpha2": "sc",
        "alpha3": "syc",
        "name": "塞舌尔"
    },
    {
        "id": 694,
        "alpha2": "sl",
        "alpha3": "sle",
        "name": "塞拉利昂"
    },
    {
        "id": 702,
        "alpha2": "sg",
        "alpha3": "sgp",
        "name": "新加坡"
    },
    {
        "id": 534,
        "alpha2": "sx",
        "alpha3": "sxm",
        "name": "荷属圣马丁"
    },
    {
        "id": 703,
        "alpha2": "sk",
        "alpha3": "svk",
        "name": "斯洛伐克"
    },
    {
        "id": 705,
        "alpha2": "si",
        "alpha3": "svn",
        "name": "斯洛文尼亚"
    },
    {
        "id": 90,
        "alpha2": "sb",
        "alpha3": "slb",
        "name": "所罗门群岛"
    },
    {
        "id": 706,
        "alpha2": "so",
        "alpha3": "som",
        "name": "索马里"
    },
    {
        "id": 710,
        "alpha2": "za",
        "alpha3": "zaf",
        "name": "南非"
    },
    {
        "id": 239,
        "alpha2": "gs",
        "alpha3": "sgs",
        "name": "南乔治亚和南桑威奇群岛"
    },
    {
        "id": 728,
        "alpha2": "ss",
        "alpha3": "ssd",
        "name": "南苏丹"
    },
    {
        "id": 724,
        "alpha2": "es",
        "alpha3": "esp",
        "name": "西班牙"
    },
    {
        "id": 144,
        "alpha2": "lk",
        "alpha3": "lka",
        "name": "斯里兰卡"
    },
    {
        "id": 729,
        "alpha2": "sd",
        "alpha3": "sdn",
        "name": "苏丹"
    },
    {
        "id": 740,
        "alpha2": "sr",
        "alpha3": "sur",
        "name": "苏里南"
    },
    {
        "id": 744,
        "alpha2": "sj",
        "alpha3": "sjm",
        "name": "斯瓦尔巴和扬马延"
    },
    {
        "id": 752,
        "alpha2": "se",
        "alpha3": "swe",
        "name": "瑞典"
    },
    {
        "id": 756,
        "alpha2": "ch",
        "alpha3": "che",
        "name": "瑞士"
    },
    {
        "id": 760,
        "alpha2": "sy",
        "alpha3": "syr",
        "name": "叙利亚"
    },
    {
        "id": 158,
        "alpha2": "tw",
        "alpha3": "twn",
        "name": "中国台湾省"
    },
    {
        "id": 762,
        "alpha2": "tj",
        "alpha3": "tjk",
        "name": "塔吉克斯坦"
    },
    {
        "id": 834,
        "alpha2": "tz",
        "alpha3": "tza",
        "name": "坦桑尼亚"
    },
    {
        "id": 764,
        "alpha2": "th",
        "alpha3": "tha",
        "name": "泰国"
    },
    {
        "id": 626,
        "alpha2": "tl",
        "alpha3": "tls",
        "name": "东帝汶"
    },
    {
        "id": 768,
        "alpha2": "tg",
        "alpha3": "tgo",
        "name": "多哥"
    },
    {
        "id": 772,
        "alpha2": "tk",
        "alpha3": "tkl",
        "name": "托克劳"
    },
    {
        "id": 776,
        "alpha2": "to",
        "alpha3": "ton",
        "name": "汤加"
    },
    {
        "id": 780,
        "alpha2": "tt",
        "alpha3": "tto",
        "name": "特立尼达和多巴哥"
    },
    {
        "id": 788,
        "alpha2": "tn",
        "alpha3": "tun",
        "name": "突尼斯"
    },
    {
        "id": 792,
        "alpha2": "tr",
        "alpha3": "tur",
        "name": "土耳其"
    },
    {
        "id": 795,
        "alpha2": "tm",
        "alpha3": "tkm",
        "name": "土库曼斯坦"
    },
    {
        "id": 796,
        "alpha2": "tc",
        "alpha3": "tca",
        "name": "特克斯和凯科斯群岛"
    },
    {
        "id": 798,
        "alpha2": "tv",
        "alpha3": "tuv",
        "name": "图瓦卢"
    },
    {
        "id": 800,
        "alpha2": "ug",
        "alpha3": "uga",
        "name": "乌干达"
    },
    {
        "id": 804,
        "alpha2": "ua",
        "alpha3": "ukr",
        "name": "乌克兰"
    },
    {
        "id": 784,
        "alpha2": "ae",
        "alpha3": "are",
        "name": "阿联酋"
    },
    {
        "id": 826,
        "alpha2": "gb",
        "alpha3": "gbr",
        "name": "英国"
    },
    {
        "id": 840,
        "alpha2": "us",
        "alpha3": "usa",
        "name": "美国"
    },
    {
        "id": 581,
        "alpha2": "um",
        "alpha3": "umi",
        "name": "美国本土外小岛屿"
    },
    {
        "id": 858,
        "alpha2": "uy",
        "alpha3": "ury",
        "name": "乌拉圭"
    },
    {
        "id": 860,
        "alpha2": "uz",
        "alpha3": "uzb",
        "name": "乌兹别克斯坦"
    },
    {
        "id": 548,
        "alpha2": "vu",
        "alpha3": "vut",
        "name": "瓦努阿图"
    },
    {
        "id": 862,
        "alpha2": "ve",
        "alpha3": "ven",
        "name": "委内瑞拉"
    },
    {
        "id": 704,
        "alpha2": "vn",
        "alpha3": "vnm",
        "name": "越南"
    },
    {
        "id": 92,
        "alpha2": "vg",
        "alpha3": "vgb",
        "name": "英属维尔京群岛"
    },
    {
        "id": 850,
        "alpha2": "vi",
        "alpha3": "vir",
        "name": "美属维尔京群岛"
    },
    {
        "id": 876,
        "alpha2": "wf",
        "alpha3": "wlf",
        "name": "瓦利斯和富图纳"
    },
    {
        "id": 732,
        "alpha2": "eh",
        "alpha3": "esh",
        "name": "西撒哈拉"
    },
    {
        "id": 887,
        "alpha2": "ye",
        "alpha3": "yem",
        "name": "也门"
    },
    {
        "id": 894,
        "alpha2": "zm",
        "alpha3": "zmb",
        "name": "赞比亚"
    },
    {
        "id": 716,
        "alpha2": "zw",
        "alpha3": "zwe",
        "name": "津巴布韦"
    }
]';

    /**
     * 获取所有代码
     * @return array|mixed
     */
    protected static function getAllCode(): mixed
    {
        try {
            return json_decode(self::$countryCode, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return [];
        }
    }

    /**
     * 通过3位国家代码获取国家名称
     * @param string $code
     * @return bool|string
     */
    public static function getNameByCode3(string $code): bool|string
    {
        $codeArray = self::getAllCode();
        foreach ($codeArray as $item) {
            if ($item['alpha3'] === $code) {
                return (string)$item['name'];
            }
        }
        return false;
    }
}