// 通讯录
//模拟数据
var contactInfo = {
  name: ["张三", "李四", "王五", "aaa", "bbb", ],
  phone: ["110", "119", "120"],
  job: ["产品", "开发", "测试"]
}
var contact_input = document.getElementById("contact_input");
var contact_search_btn = get_qs(".section_contact_search");
var contactTips = get_qs('.contactTips');

contact_input.addEventListener('input', function () {
  var searchVal = contact_input.value;
  if (!searchVal || searchVal == "") {
    disNone(contactTips);
  }
})

contact_input.addEventListener('focus', function () {
  document.onkeyup = function (e) {
    var e = e || window.event;
    if (e && e.keyCode == 13) {
      searchTip();
    }
  }
})

contact_search_btn.addEventListener("click", searchTip);

function searchTip() {
  contactTips.innerHTML = "";
  var searchVal = contact_input.value;
  if (searchVal && !searchVal == "") {
    for (var i = 0; i < contactInfo.name.length; i++) {
      if (contactInfo.name[i].indexOf(searchVal) == 0 || contactInfo.name[i].indexOf(searchVal) >
        0) {
        var newTip =
          '<a href = "otherPerson.html" target="_blank"><li class="contactTip"><span>' +
          contactInfo.name[i] + '</span><span>' +
          contactInfo.phone[i] + '</span><span>' + contactInfo.job[i] + '</span></li></a>';
        contactTips.innerHTML += newTip;
        disBlock(contactTips);
      }

    }
    for (var i = 0; i < contactInfo.phone.length; i++) {
      if (contactInfo.phone[i].indexOf(searchVal) == 0 || contactInfo.phone[i].indexOf(searchVal) >
        0) {
        var newTip =
          '<a href = "otherPerson.html" target="_blank"><li class="contactTip"><span>' +
          contactInfo.name[i] + '</span><span>' +
          contactInfo.phone[i] + '</span><span>' + contactInfo.job[i] + '</span></li></a>';
        contactTips.innerHTML += newTip;
        disBlock(contactTips);
      }
    }
    for (var i = 0; i < contactInfo.job.length; i++) {
      if (contactInfo.job[i].indexOf(searchVal) == 0 || contactInfo.job[i].indexOf(searchVal) > 0) {
        var newTip =
          '<a href = "otherPerson.html" target="_blank"><li class="contactTip"><span>' +
          contactInfo.name[i] + '</span><span>' +
          contactInfo.phone[i] + '</span><span>' + contactInfo.job[i] + '</span></li></a>';
        contactTips.innerHTML += newTip;
        disBlock(contactTips);
      }
    }
  }
}