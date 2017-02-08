/**
 * @description 一进入首页时信息的展示
 * @author <huixiang0922@gmail.com>
 * @date 14-7-4 10:25
 */

define(function(require, exports, module) {
    var $ = require('$');
    var Controller = require('./common/controller');
    require('../lib/cmp/tree/ztree/ztree');
    var GetStaff = require('./get-staff');
    var OperateTree = require('./operate-tree');
    var CommonFun = require('./common/action/action');
    var dataArr = [];

    var showInfo = {
        //请求加载节点树
        init: function(){
            Controller.orgManager.getDepts(function(data){

                var setting = {
                    edit: {
                        drag: {
                            autoExpandTrigger: true,
                            prev: dropPrev,
                            inner: dropInner,
                            next: dropNext
                        },
                        enable: true,
                        showRemoveBtn: false,
                        showRenameBtn: false
                    },
                    data: {
                        simpleData: {
                            enable: true
                        }
                    },
                    callback: {
                        beforeDrag: beforeDrag,
                        beforeDrop: beforeDrop,
                        beforeDragOpen: beforeDragOpen,
                        onDrag: onDrag,
                        onDrop: onDrop,
                        onExpand: onExpand
                    }
                };
                function dropPrev(treeId, nodes, targetNode) {
                    var pNode = targetNode.getParentNode();
                    if (pNode && pNode.dropInner === false) {
                        return false;
                    } else {
                        for (var i=0,l=curDragNodes.length; i<l; i++) {
                            var curPNode = curDragNodes[i].getParentNode();
                            if (curPNode && curPNode !== targetNode.getParentNode() && curPNode.childOuter === false) {
                                return false;
                            }
                        }
                    }
                    return true;
                }
                function dropInner(treeId, nodes, targetNode) {
                    if (targetNode && targetNode.dropInner === false) {
                        return false;
                    } else {
                        for (var i=0,l=curDragNodes.length; i<l; i++) {
                            if (!targetNode && curDragNodes[i].dropRoot === false) {
                                return false;
                            } else if (curDragNodes[i].parentTId && curDragNodes[i].getParentNode() !== targetNode && curDragNodes[i].getParentNode().childOuter === false) {
                                return false;
                            }
                        }
                    }
                    return true;
                }
                function dropNext(treeId, nodes, targetNode) {
                    var pNode = targetNode.getParentNode();
                    if (pNode && pNode.dropInner === false) {
                        return false;
                    } else {
                        for (var i=0,l=curDragNodes.length; i<l; i++) {
                            var curPNode = curDragNodes[i].getParentNode();
                            if (curPNode && curPNode !== targetNode.getParentNode() && curPNode.childOuter === false) {
                                return false;
                            }
                        }
                    }
                    return true;
                }

                var log, className = "dark", curDragNodes, autoExpandNode;
                function beforeDrag(treeId, treeNodes) {
                    className = (className === "dark" ? "":"dark");
                    showLog("[ "+getTime()+" beforeDrag ]&nbsp;&nbsp;&nbsp;&nbsp; drag: " + treeNodes.length + " nodes." );
                    for (var i=0,l=treeNodes.length; i<l; i++) {
                        if (treeNodes[i].drag === false) {
                            curDragNodes = null;
                            return false;
                        } else if (treeNodes[i].parentTId && treeNodes[i].getParentNode().childDrag === false) {
                            curDragNodes = null;
                            return false;
                        }
                    }
                    curDragNodes = treeNodes;
                    return true;
                }
                function beforeDragOpen(treeId, treeNode) {
                    autoExpandNode = treeNode;
                    return true;
                }
                function beforeDrop(treeId, treeNodes, targetNode, moveType, isCopy) {
                    className = (className === "dark" ? "":"dark");
                    showLog("[ "+getTime()+" beforeDrop ]&nbsp;&nbsp;&nbsp;&nbsp; moveType:" + moveType);
                    showLog("target: " + (targetNode ? targetNode.name : "root") + "  -- is "+ (isCopy==null? "cancel" : isCopy ? "copy" : "move"));
                    return true;
                }
                function onDrag(event, treeId, treeNodes) {
                    className = (className === "dark" ? "":"dark");
                    showLog("[ "+getTime()+" onDrag ]&nbsp;&nbsp;&nbsp;&nbsp; drag: " + treeNodes.length + " nodes." );
                }
                function onDrop(event, treeId, treeNodes, targetNode, moveType, isCopy) {
                    className = (className === "dark" ? "":"dark");
                    showLog("[ "+getTime()+" onDrop ]&nbsp;&nbsp;&nbsp;&nbsp; moveType:" + moveType);
                    showLog("target: " + (targetNode ? targetNode.name : "root") + "  -- is "+ (isCopy==null? "cancel" : isCopy ? "copy" : "move"))
                    //干活，排序修改pid
                    if (isCopy!==null) {
                        showLog("[ "+getTime()+" onDrop ]&nbsp;&nbsp;&nbsp;&nbsp;pid=" + targetNode.id+",pid=" + treeNodes[0].id);

                        if ($.fn.zTree.consts.move.TYPE_PREV === moveType) {
                            treeNodes[0].seq = targetNode.seq-1 < 0 ? 0 : targetNode.seq;
                        } else if ($.fn.zTree.consts.move.TYPE_NEXT === moveType) {
                            treeNodes[0].seq = targetNode.seq+1;
                        }

                        var data = {
                            'dept_id': treeNodes[0].id,
                            'parent_id': +treeNodes[0].pId,
                            'seq': treeNodes[0].seq
                        };
                        Controller.orgManager.edit(data);
                        //console.log(treeNodes,targetNode);
                    }
                }
                function onExpand(event, treeId, treeNode) {
                    if (treeNode === autoExpandNode) {
                        className = (className === "dark" ? "":"dark");
                        showLog("[ "+getTime()+" onExpand ]&nbsp;&nbsp;&nbsp;&nbsp;" + treeNode.name);
                    }
                }

                function showLog(str) {
                    //console.log(str);
                    //if (!log) log = $("#log");
                    //log.append("<li class='"+className+"'>"+str+"</li>");
                    //if(log.children("li").length > 8) {
                    //    log.get(0).removeChild(log.children("li")[0]);
                    //}
                }
                function getTime() {
                    var now= new Date(),
                        h=now.getHours(),
                        m=now.getMinutes(),
                        s=now.getSeconds(),
                        ms=now.getMilliseconds();
                    return (h+":"+m+":"+s+ " " +ms);
                }


                var treeArr = showInfo.mapData(data);
                //默认设置树的第一个节点展开，并设置展开样式
                treeArr[0].open = true;

                Global.treeObj = $.fn.zTree.init($('#classTree'), setting, treeArr);

                //设置当前节点
                //$('#classTree a:first').addClass('curSelectedNode');
                //获取选中节点的id值
                //Global.curA = $('#classTree a.curSelectedNode');
                //var curId = GetStaff.getSelectedNode(Global.curA, Global.treeObj).id;
                //通过curId获取数据
                //GetStaff.staffList(Global.curA, curId);

                $('#companyName').addClass("curSelected");
                GetStaff.staffList($('#companyName'), -1);

                //操作树
                OperateTree.init();

                //文本溢出及弹出操作节点层级的定位
                CommonFun.treeStyle($('#classTree a'));
            })

        },
        //处理返回的json数据，把数组回传给节点树
        mapData: function(data){

            for (var i = 0; i < data.length; i++){

                var singleData = {};
                singleData.id = data[i].dept_id;
                singleData.pId = data[i].parent_id;
                singleData.name = data[i].name;
                //singleData.spell1 = data[i].spell1;
                //singleData.spell2 = data[i].spell2;
                singleData.seq = data[i].seq;

                dataArr.push(singleData);
                if (data[i].sub){
                    arguments.callee(data[i].sub);
                }

            }

            return dataArr;
        }
    };

    module.exports = showInfo;
})
