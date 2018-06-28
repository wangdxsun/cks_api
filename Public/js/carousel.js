var showbox = document.getElementById('showbox');
var pics = document.getElementById('pics');
var swapButtons = document.querySelectorAll("#swapButtons div");


function swap(x) {
    var picsleft = parseInt(pics.style.left) + x;
    pics.style.left = picsleft + 'px';
    if (picsleft == -3180) {
        pics.style.left = 0 + 'px';
    }
    if (picsleft == 1060) {
        pics.style.left = -2120 + 'px';
    }
}
next.onclick = function () {
    swap(-1060);
    showButton();
}
prev.onclick = function () {
    swap(1060);
    showButton();
}

function play() {
    timer = setInterval(function () {
        next.onclick();
    }, 2000);
}

function stop() {
    clearInterval(timer);
}
showbox.onmouseout = play;
showbox.onmouseover = stop;
play();

function showButton() {
    var leftCount = ((parseInt(pics.style.left)) / (-1060));
    for (var i = 0; i < swapButtons.length; i++) {
        swapButtons[i].className = '';
    }
    swapButtons[leftCount].className = 'on';
}

for (var i = 0; i < swapButtons.length; i++) {
    swapButtons[i].onclick = btnChange.bind(null, i);
}

function btnChange(index) {
    if (swapButtons[index].style.className == 'on') {
        return;
    }
    for (var i = 0; i < swapButtons.length; i++) {
        swapButtons[i].className = '';
    }
     swapButtons[index].className = 'on';
    pics.style.left = -(1060*index) + "px";
};