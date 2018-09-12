document.addEventListener("DOMContentLoaded", function() {
  var el = document.querySelector(".hamburger");

  el.onclick = function() {
    el.classList.toggle("expanded");
    //window.alert("hello");
   document.write(6+7);
  };
});
