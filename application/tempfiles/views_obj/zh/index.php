<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $unit_name?>-组织管理</title>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="<?php echo BASE_URL?>/static/themes/default/css/tree/zTreeStyle.min.css" type="text/css" />
    <link rel="stylesheet" href="<?php echo BASE_URL?>/static/themes/default/css/calendar.min.css" type="text/css" />
    <link href="<?php echo BASE_URL?>/static/themes/default/css/index.min.css" rel="stylesheet" type="text/css" />
    <style>.main_content.ovh {margin-left: 220px; position: fixed;width: 1030px;}#companyName{cursor:pointer}</style>
  </head>
  <body>
    <div class="person_msg dn"></div>
    <div id="wrap" class="ovh">
      <div class="class_list">
        <h1 id="companyName" title="<?php echo $unit_name?>"><?php echo $unit_name?></h1>
        <ul id="classTree" class="ztree"></ul>
        <a href="javascript:;" data-action="add" class="btn_add"><span>+</span>新增部门</a>
      </div>
      <div class="main_content ovh">
        <div id="main_title">
          <div class="tx">
            <h2 id="selectTitle" title="0" class="select_text"></h2>
            <!--// <p>如需进行批量修改，只要在模板中修改后重新导入即可!</p> //-->
          </div>
          <div class="btn_area">
            <div class="add" title="新增帐号" data-action="addStaff"></div>
            <!--// <div class="ipt" title="导入" data-action="ipt"></div> //-->
            <!--// <div class="opt" title="导出" data-action="opt"></div> //-->
            <!--// <div class="rpwd" title="重置密码" data-action="resetPwd"></div> //-->
            <div class="del" title="删除" data-action="delStaff"></div>
            <!--// <div class="appeal" title="设置初始密码" data-action="setPwd"></div> //-->
          </div>
        </div>
        <div id="data_show">
          <div class="clearfix" id="stu_list_tip">
            <div class="fl">共有<b>0</b>人</div>
            <div class="search_box fr">
              <input type="text" placeholder="搜索姓名/手机号" id="searchText" />
              <div class="search_btn" data-action="searchBtn"></div>
            </div>
          </div>
          <div id="dataList">
          </div>
          <div id="pagination"></div>
        </div>
      </div>
    </div>
    <script src="<?php echo BASE_URL?>/static/js/seajs/sea-debug.js" type="text/javascript"></script>
    <script src="<?php echo BASE_URL?>/static/js/config.js" type="text/javascript"></script>
    <script type="text/javascript">
      var Global = {
        unitid: '<?php echo $unti_id?>',
        unitname: '<?php echo $unit_name?>',
        req_url: '<?php echo BASE_URL?>' + '/api',
        treeObj: null,
        base_url: '<?php echo BASE_URL?>'
      };
      seajs.use('dist/app/index');
    </script>
  </body>
</html>