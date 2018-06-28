//funcs
var get_qs = function (sl) {
    return document.querySelector(sl);
}
var get_cn = function (cls) {
    return document.getElementsByClassName(cls);
}
var hideScroll = function () {
    document.documentElement.style.overflow = 'hidden';
    document.body.style.marginRight = "17px";
}
var keepScroll = function () {
    document.documentElement.style.overflow = 'visible';
    document.body.style.marginRight = "0";
}
var disBlock = function (ele) {
    ele.style.display = "block";
}
var disNone = function (ele) {
    ele.style.display = "none";
}

function randomColor() {
    var x = parseInt(Math.random() * 255, 10);
    var y = parseInt(Math.random() * 255, 10);
    var z = parseInt(Math.random() * 255, 10);
    return "rgb(" + x + "," + y + "," + z + ")";
}
//模拟公告数据，实际为ajax获取json
var jsonData = {
    title: ["[新动力OKR模块上线]", "中美对话——记12期 PHICOMM Free Talk", "中美对话——记12期 PHICOMM Free Talk",
        "中美对话——记12期 PHICOMM Free Talk"
    ],
    content: [
        "Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Typi non habent claritatem insitam; est usus legentis in iis qui facit eorum claritatem.Investigationes demonstraverunt lectores legere me lius quod ii legunt saepius. Claritas est etiam processus dynamicus, qui sequitur mutationem consuetudium lectorum. Mirum est notare quam littera gothica, quam nunc putamus parum claram, anteposuerit litterarum formas humanitatis per seacula quarta decima et quinta decima. Eodem modo typi, qui nunc nobis videntur parum clari, fiant sollemnes in futurum.",
        "Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Typi non habent claritatem insitam; est usus legentis in iis qui facit eorum claritatem.Investigationes demonstraverunt lectores legere me lius quod ii legunt saepius. Claritas est etiam processus dynamicus, qui sequitur mutationem consuetudium lectorum. Mirum est notare quam littera gothica, quam nunc putamus parum claram, anteposuerit litterarum formas humanitatis per seacula quarta decima et quinta decima. Eodem modo typi, qui nunc nobis videntur parum clari, fiant sollemnes in futurum.",
        "Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Typi non habent claritatem insitam; est usus legentis in iis qui facit eorum claritatem.Investigationes demonstraverunt lectores legere me lius quod ii legunt saepius. Claritas est etiam processus dynamicus, qui sequitur mutationem consuetudium lectorum. Mirum est notare quam littera gothica, quam nunc putamus parum claram, anteposuerit litterarum formas humanitatis per seacula quarta decima et quinta decima. Eodem modo typi, qui nunc nobis videntur parum clari, fiant sollemnes in futurum.",
        "Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit lobortis nisl ut aliquip ex ea commodo consequat. Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi.Nam liber tempor cum soluta nobis eleifend option congue nihil imperdiet doming id quod mazim placerat facer possim assum. Typi non habent claritatem insitam; est usus legentis in iis qui facit eorum claritatem.Investigationes demonstraverunt lectores legere me lius quod ii legunt saepius. Claritas est etiam processus dynamicus, qui sequitur mutationem consuetudium lectorum. Mirum est notare quam littera gothica, quam nunc putamus parum claram, anteposuerit litterarum formas humanitatis per seacula quarta decima et quinta decima. Eodem modo typi, qui nunc nobis videntur parum clari, fiant sollemnes in futurum."
    ],
    imgSrc: ["", "img/testImg.jpg", "img/testImg.jpg", "img/testImg.jpg"]
}

$(document).ready(function() {

    $(".section_notice_title").click(function(event) {
        $(this).parents(".section_notice2").find('.section_notice_content').toggle(200);
    });

    $(".header_userArea_funcs.set").click(function() {
        $(".sethidden").toggle();
    });
});



//模拟渲染，实际为ajax回调
/*var boardTitles = get_cn('section_notice_content_title'),
    boardContent = get_cn('section_notice_content_text'),
    boardImgs = get_cn('section_notice_content_img');
if (boardImgs.length > 0 && boardTitles.length > 0 || boardContent > 0) {
    for (var i = 0; i < jsonData.title.length; i++) {
        boardImgs[i].src = jsonData.imgSrc[i];
        if (jsonData.imgSrc.length == 0 || jsonData.imgSrc[i] == "" || jsonData.imgSrc[i] == undefined || !jsonData.imgSrc[i]) {
            disNone(boardImgs[i]);
        }
        boardTitles[i].innerHTML = jsonData.title[i];
        boardContent[i].innerHTML = jsonData.content[i];
    }
}*/
//公告折叠/显示
// var toggleBtns = get_cn('section_notice_title');
// var toogleContents = get_cn('section_notice');
// var toggleArrows = get_cn('section_notice_toggle');
// var isShow = new Array();

//初始化卡片高度
// for (var i = 0; i < toggleBtns.length; i++) {
//     isShow[i] = true;
//     var temH = window.getComputedStyle(toogleContents[i]).height;
//     toogleContents[i].style.height = temH;
//     toggleBtns[i].onclick = toggleContent.bind(null, i, temH);
// }

// function toggleContent(i, temH) {
    
//     if (toogleContents[i].id && toogleContents[i].id == "personInfo") {
//         return;
//     } else {
//         if (isShow[i] == true) {
//             toogleContents[i].style.height = "40px";
//             if(toogleContents[i].id == "section_contact"){
//             toogleContents[i].style.overflow = "hidden";
//             }
//             toggleArrows[i].style.transform = "rotate(-180deg)";
//             toggleBtns[i].style.borderBottom = "none";
//             isShow[i] = false;
//             return;
//         }
//         //展开
//         else {
//             toogleContents[i].style.height = temH;
//             if(toogleContents[i].id == "section_contact"){
//             toogleContents[i].style.overflow = "visible";
//             }
//             toggleArrows[i].style.transform = "rotate(0deg)";
//             toggleBtns[i].style.borderBottom = "1px solid #999";
//             isShow[i] = true;
//             return;
//         }
//     }
// }

//文章跳转
/*var articles = get_cn("article");
for(var i = 0;i<articles.length;i++){
    articles[i].onclick = function(){
        //ajax
        window.open(this.getAttribute('jump'),"_blank")
    }
}*/