function openPage(pageName, el) {
  var i, tabcontent, tablinks;
  tabs = document.getElementsByClassName("tab-content");
  for (i = 0; i < tabs.length; i++) {
    tabs[i].style.display = "none";
  }
  tablinks = document.getElementsByClassName("tab");
  for (i = 0; i < tablinks.length; i++) {
	tablinks[i].classList.remove('activeTab');
  }
  document.getElementById(pageName).style.display = "block";
  el.classList.add('activeTab');
}