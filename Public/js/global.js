//全局函数
var globalFn = {
    //page  总页数
    //jumpurl 模块/控制器/方法
    lypage:function (id, page, jumpUrl, field, val) {
        layui.use('laypage', function () {
            var laypage = layui.laypage;
            laypage({
                cont: id,
                pages: page,
                skip: true,
                curr: function () { //通过url获取当前页，也可以同上（pages）方式获取
                    var page = location.search.match(/page=(\d+)/);
                    return page ? page[1] : 1;
                }(),
                jump: function (obj, first) { //触发分页后的回调
                    if (!first) { //一定要加此判断，否则初始时会无限刷新
                        var currentPage = obj.curr;//获取点击的页码
                        window.location.href = "/index.php/"+jumpUrl+".html?page=" + currentPage+'&'+field+'='+val;
                    } else {
                    }
                }
            });
        })
    },

    //ly弹窗封装
    lyPopup:function(title, area, id, close, shadeClose){
        layui.use('layer', function(){
            var layer = layui.layer;
            layer.open({
                title: title,
                type: 1,
                area: area,
                scrollbar: false,
                content: $('#'+id),
                closeBtn: close,
                shadeClose: shadeClose ? true : false,
            });
        });
    },

    //layer单选
    checkRadio:function(radioVal){
            $('.layui-form-radio').each(function(i){
                //var lyi = $($(this).children("i").get(0));
                var lyi = $($(this).children("i"));
                if(i == radioVal){
                    !$(this).hasClass('layui-form-radioed') && $(this).addClass('layui-form-radioed');
                    !lyi.hasClass('layui-anim-scaleSpring') && lyi.addClass('layui-anim-scaleSpring');
                    lyi.html('&#xe643;');
                }else{
                    $(this).hasClass('layui-form-radioed') && $(this).removeClass('layui-form-radioed');
                    lyi.hasClass('layui-anim-scaleSpring') && lyi.removeClass('layui-anim-scaleSpring');
                    lyi.html('&#xe63f;');
                }

            })

    },

    //ajax回调提醒
    remind:function(index, info, jumpUrl){
        setTimeout(function () { layer.close(index); }, 1000);
        setTimeout(function () { layer.msg(info) }, 1000);
        jumpUrl && setTimeout(function(){window.location.href = jumpUrl}, 2000);
    },

    //上传图片
    webUpload:function(idArr, file){
        // 初始化Web Uploader
        var uploader = WebUploader.create({

            // 选完文件后，是否自动上传。
            auto: true,
            // swf文件路径
            swf: '/Public/js/webuploader-0.1.5/Uploader.swf',
            // 文件接收服务端。
            server: "/index.php/Admin/Base/upload.html?path="+file,
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: '#filePicker',
            // 只允许选择图片文件。
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/jpg,image/jpeg,image/png'
            }
        });

        // 文件上传成功
        uploader.on( 'uploadSuccess', function( file, res) {
            $('#'+idArr[0]).val(res.bigImg); //大图地址  input
            $('#'+idArr[1]).val(res.thumb); //缩略图地址 input
            idArr[2] && $('#'+idArr[2]+' img').attr('src', res.thumb);
            idArr[3] && $('#'+idArr[3]+' img').attr('src', res.thumb);
            globalFn.popupMsg('上传成功');
            console.log();
        });

        // 文件上传失败，显示上传出错。
        uploader.on( 'uploadError', function( file ) {
            globalFn.popupMsg('上传失败');
        });
    },

    //弹窗msg
    popupMsg:function(text){
        layui.use('layer', function(){
            layui.layer.msg(text);
        });
    },

    //预览
    preview: function(id){
        $('#preview').click(function(){
            $('#'+id+' img').attr('src')
                ? globalFn.lyPopup(false, ['auto'], id, 0, true)
                : globalFn.popupMsg('请先上传图片');
        })
    },

    //点击图片放大
    zoomify:function(){
        //缩小绑定事件隐藏
        var $zoomify = $('.example img');
        $zoomify.zoomify().on({
            'zoom-out-complete.zoomify': function() {
                $('.example img').hide();
            },
        });

        //点击放大的方法
        $('.zoomIn').on('click', function() {
            var _thisId = '#big'+$(this).attr('id');
            $(_thisId+' img').show();
            $(_thisId+' img').zoomify('zoomIn');
        });
    },

    //lyform
    lyformRender:function(type){
        layui.use(['form'], function(){
            var form = layui.form();
            form.render(type)
        });
    }
}



/*layui.use('laypage', function () {
 var laypage = layui.laypage;
 laypage({
 cont: 'demo7',
 pages: "<?php echo $data['pages']?>",
 skip: true,
 curr: function () { //通过url获取当前页，也可以同上（pages）方式获取
 var page = location.search.match(/page=(\d+)/);
 return page ? page[1] : 1;
 }(),
 jump: function (obj, first) { //触发分页后的回调
 if (!first) { //一定要加此判断，否则初始时会无限刷新
 var currentPage = obj.curr;//获取点击的页码
 window.location.href = "/index.php/Admin/SystemLink/index.html?page=" + currentPage;
 } else {
 }
 }
 });
 })*/