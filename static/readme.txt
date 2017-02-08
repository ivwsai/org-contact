目录结构说明，对于第三方库，例如jquery-ui-1.8.16.custom.min.js, 在添加到项目中来的时候必须改名为jquery.min.js,
第三方库目录下添加一个license.txt文件注明版本号跟相关信息
原因是项目中使 <script src="jquery-ui-1.8.16.custom.min.js"></script>
升级版本后必须替换项目中的文件，不然会出现名称号版本号不对应


static/themes
│
├─lib(第三方JS库;第三方库用到的CSS images必须移到default,metro,etc..中的CSS目录下)
│  ├─Datepicker
│  │  └─css(项目中不能直接使用该处资源，必须放到default,metro,etc..中的CSS目录下)
│  │      │  default.css
│  │      └─images
│  ├─pure
│  │  └─css
│  │      │  default.css
│  │      └─images
│  └─ztree
│      └─css
│          │  default.css
│          └─images
│
├─edk(项目自己开发的库、或者不涉及到样式展现的一些业务)
│  │  proxy.js
│  └─3G(手机版特有的JS)
│       iscroll.js
│
├─default
│  ├─css
│  │  │  default.css
│  │  ├─images
│  │  ├─Datepicker(第三方库样式文件)
│  │  │  │  default.css
│  │  │  └─images
│  │  ├─pure
│  │  │  │  default.css
│  │  │  └─images
│  │  └─ztree
│  │      │  default.css
│  │      └─images
│  ├─images(公共图片存放地址)
│  └─js
│      ├─disk(具体实现业务的JS代码文件)
│      └─im
│
└─metro
    ├─css
    │  │  default.css
    │  ├─images
    │  ├─Datepicker
    │  │  │  default.css
    │  │  └─images
    │  ├─pure
    │  │  │  default.css
    │  │  └─images
    │  └─ztree
    │      │  default.css
    │      └─images
    ├─images
    └─js
        ├─disk
        └─im


============================================================================================================================
static/themes
│
├─lib(第三方JS库)
│  ├─Datepicker
│  │  └─css(项目中不能直接使用该处资源，必须放到default,metro,etc..中)
│  │      │  default.css
│  │      │  
│  │      └─images
│  ├─Flexigrid
│  │  └─css
│  │      │  default.css
│  │      │  
│  │      └─images
│  ├─Flot
│  │  └─css
│  │      │  default.css
│  │      │  
│  │      └─images
│  ├─kindeditor
│  │  └─css
│  │      │  default.css
│  │      │  
│  │      └─images
│  ├─lightBox
│  │  └─css
│  │      │  default.css
│  │      │  
│  │      └─images
│  ├─pagination
│  │  └─css
│  │      │  default.css
│  │      │  
│  │      └─images
│  ├─Progress Bar
│  │  └─css
│  │      │  default.css
│  │      │  
│  │      └─images
│  ├─pure
│  │  └─css
│  │      │  default.css
│  │      │  
│  │      └─images
│  ├─swfupload
│  │  └─css
│  │      │  default.css
│  │      │  
│  │      └─images
│  ├─thickbox
│  │  └─css
│  │      │  default.css
│  │      │  
│  │      └─images
│  └─ztree
│      └─css
│          │  default.css
│          │  
│          └─images
├─default
│  ├─css
│  │  │  default.css
│  │  │  
│  │  ├─Datepicker(第三方库样式文件)
│  │  │  │  default.css
│  │  │  │  
│  │  │  └─images
│  │  ├─Flexigrid
│  │  │  │  default.css
│  │  │  │  
│  │  │  └─images
│  │  ├─Flot
│  │  │  │  default.css
│  │  │  │  
│  │  │  └─images
│  │  ├─images
│  │  ├─kindeditor
│  │  │  │  default.css
│  │  │  │  
│  │  │  └─images
│  │  ├─lightBox
│  │  │  │  default.css
│  │  │  │  
│  │  │  └─images
│  │  ├─pagination
│  │  │  │  default.css
│  │  │  │  
│  │  │  └─images
│  │  ├─Progress Bar
│  │  │  │  default.css
│  │  │  │  
│  │  │  └─images
│  │  ├─pure
│  │  │  │  default.css
│  │  │  │  
│  │  │  └─images
│  │  ├─swfupload
│  │  │  │  default.css
│  │  │  │  
│  │  │  └─images
│  │  ├─thickbox
│  │  │  │  default.css
│  │  │  │  
│  │  │  └─images
│  │  └─ztree
│  │      │  default.css
│  │      │  
│  │      └─images
│  ├─images
│  └─js
│      ├─disk
│      └─im
├─edk(项目自己开发的库、或者不涉及到样式展现的一些业务)
│  │proxy.js
│  └3G
│     iscroll.js
└─metro
    │  default.css
    │  
    ├─Datepicker
    │  │  default.css
    │  │  
    │  └─images
    ├─Flexigrid
    │  │  default.css
    │  │  
    │  └─images
    ├─Flot
    │  │  default.css
    │  │  
    │  └─images
    ├─images
    ├─js
    │  ├─disk
    │  └─im
    ├─kindeditor
    │  │  default.css
    │  │  
    │  └─images
    ├─lightBox
    │  │  default.css
    │  │  
    │  └─images
    ├─pagination
    │  │  default.css
    │  │  
    │  └─images
    ├─Progress Bar
    │  │  default.css
    │  │  
    │  └─images
    ├─pure
    │  │  default.css
    │  │  
    │  └─images
    ├─swfupload
    │  │  default.css
    │  │  
    │  └─images
    ├─thickbox
    │  │  default.css
    │  │  
    │  └─images
    └─ztree
        │  default.css
        │  
        └─images
