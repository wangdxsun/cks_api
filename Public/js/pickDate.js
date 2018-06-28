function YYYYMMDDstart() {
    MonHead = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    //先给年下拉框赋内容   
    var y = new Date().getFullYear();
    for (var i = (y - 30); i < (y + 30); i++) //以今年为准，前30年，后30年   
        document.getElementById('in_YYYY').options.add(new Option(" " + i + " 年", i));
    //赋月份的下拉框   
    for (var i = 1; i < 13; i++)
        document.getElementById('in_MM').options.add(new Option(" " + i + " 月", i));

    document.getElementById('in_YYYY').value = y;
    document.getElementById('in_MM').value = new Date().getMonth() + 1;
    var n = MonHead[new Date().getMonth()];
    if (new Date().getMonth() == 1 && IsPinYear(YYYYvalue)) n++;
}
function outYYYYMMDDstart() {
    MonHead = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

    //先给年下拉框赋内容   
    var y = new Date().getFullYear();
    for (var i = (y - 30); i < (y + 30); i++) //以今年为准，前30年，后30年   
        document.getElementById('out_YYYY').options.add(new Option(" " + i + " 年", i));
    //赋月份的下拉框   
    for (var i = 1; i < 13; i++)
        document.getElementById('out_MM').options.add(new Option(" " + i + " 月", i));

    document.getElementById('out_YYYY').value = y;
    document.getElementById('out_MM').value = new Date().getMonth() + 1;
    var n = MonHead[new Date().getMonth()];
    if (new Date().getMonth() == 1 && IsPinYear(YYYYvalue)) n++;
}

window.onload = function () {
    YYYYMMDDstart();
    outYYYYMMDDstart();
}

function YYYYDD(str) //年发生变化时日期发生变化(主要是判断闰平年)   
{
    var MMvalue = document.getElementById('in_MM').options[document.getElementById('in_MM').selectedIndex].value;
    if (MMvalue == "") {
        optionsClear(e);
        return;
    }
    var n = MonHead[MMvalue - 1];
    if (MMvalue == 2 && IsPinYear(str)) n++;
}

function outYYYYDD(str) //年发生变化时日期发生变化(主要是判断闰平年)   
{
    var MMvalue = document.getElementById('out_MM').options[document.getElementById('out_MM').selectedIndex].value;
    if (MMvalue == "") {
        optionsClear(e);
        return;
    }
    var n = MonHead[MMvalue - 1];
    if (MMvalue == 2 && IsPinYear(str)) n++;
}

function IsPinYear(year) //判断是否闰平年   
{
    return (0 == year % 4 && (year % 100 != 0 || year % 400 == 0));
}

function optionsClear(e) {
    e.options.length = 1;
}