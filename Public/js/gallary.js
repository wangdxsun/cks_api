        //gallary init
        var gallary_imgs = document.querySelector('.gallary_imgs');
        var allImgs = document.getElementsByClassName('gallary_img_item');
        var gallary_btn_left = document.querySelector(".gallary_btn_left");
        var gallary_btn_right = document.querySelector(".gallary_btn_right");
        var img_num = document.getElementsByClassName('gallary_img_item').length;
        var borderLength = img_num * 140 - 420;

        //随机颜色填充，仅作相册展示测试
        for (var i = 0; i < allImgs.length; i++) {
            if($(allImgs[i]).attr('attr') != 1)
            allImgs[i].style.background = randomColor();
        }

        function randomColor() {
            var x = parseInt(Math.random() * 255, 10);
            var y = parseInt(Math.random() * 255, 10);
            var z = parseInt(Math.random() * 255, 10);
            return "rgb(" + x + "," + y + "," + z + ")";
        }

        //相册切换
        gallary_btn_left.onclick = function () {
            var temLeft = parseInt(gallary_imgs.currentStyle ? gallary_imgs.currentStyle : window.getComputedStyle(gallary_imgs, null).left);
            if (temLeft == 0) {
                return;
            } else {
                gallary_imgs.style.left = temLeft + 140 + 'px';
            }
        }
        gallary_btn_right.onclick = function () {
            var temLeft = parseInt(gallary_imgs.currentStyle ? gallary_imgs.currentStyle : window.getComputedStyle(gallary_imgs, null).left);
            if (temLeft == -borderLength) {
                return;
            } else {
                gallary_imgs.style.left = temLeft - 140 + 'px';
            }
        }