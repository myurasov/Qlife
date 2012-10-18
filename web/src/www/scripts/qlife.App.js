/**
 * Qlife app
 * @copyright 2012, Mikhail Yurasov
 */

if (typeof qlife == "undefined") qlife = {};

qlife.App = function (data) {

  var self = this;

  function _init() {
    self.data = data;
    self.pageData = data.pageData;
    fixProfileWidth();

    if (self.data.user) {
      self.settingsModal = new mym.Modal($('#settingsModal'));
    }

    //

    $(".header .userMenu .settings").click(function () {
//      self.settingsModal.open({
//        effect: "unfold"
//      });
    });
  }

  // profile width fix
  function fixProfileWidth() {
    var f = function () {
     $(".profileBlock").each(function () {
       var w = $(".content").width() - $(".avatar", this).outerWidth(true);
       $(".section", this).animate({
         width: w
       });
     });
    }

    $(window).load(f);
    $("profileBlock .avatar img").load(f);
  }

  self.initProfile = function () {
    self.profile = new qlife.Profile({
      element: ".content"
    });
  }

  _init();
}