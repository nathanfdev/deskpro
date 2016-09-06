Orb.createNamespace('DeskPRO.Agent.ElementHandler');

DeskPRO.Agent.ElementHandler.GoToBilling = new Orb.Class({
  Extends: DeskPRO.ElementHandler,

  initPage: function () {
    this.el.on('click', function(ev) {
      if (window.parent && window.DP_FRAME_OVERLAYS && window.DP_FRAME_OVERLAYS.admin) {
        Orb.cancelEvent(ev);
        window.parent.DP_FRAME_OVERLAYS.admin.open('/license');
      }
    });
  }
});
