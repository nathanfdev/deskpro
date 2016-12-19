Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.VoiceCallInProgress = new Orb.Class({

  Extends: DeskPRO.Agent.PageFragment.Basic,

  initializeProperties: function() {
    this.parent();
    this.TYPENAME = 'voice-call';
  },

  initPage: function(el) {
    this.el = el;

    var self = this;
    var onEndCall = function() {
      self.meta.title = 'Call Ended';

      if (DeskPRO_Window.TabBar) {
        DeskPRO_Window.TabBar.rescanTitles();
      }
    };

    var node = document.getElementById(this.meta.baseId + '_react_container');
    var controls = window.AgentLegacyBundle.renderVoiceControls(node, parseInt(this.meta.callId, 10), onEndCall);

    var confirmCloseOverlay = new DeskPRO.UI.Overlay({
      contentElement: this.getEl('closetab_prompt'),
      addClassname: 'normal-size',
      onPosition: function(evData) {
        var tabId = self.getTabId();
        if (!tabId) return;

        var tabEl = $('#tabbtn_' + tabId);
        if (!tabEl[0]) {
          return;
        }
        var tabW = tabEl.width();

        evData.left = (tabEl.offset().left + (tabW / 2)) - (evData.w / 2);
        evData.top = tabEl.offset().top;

        if ((evData.left + evData.w) > evData.pageW) {
          evData.left = evData.pageW - evData.w - 15;
        }
      },
      onContentSet: function() {
        $('.end-trigger').on('click', function() {
          confirmCloseOverlay.close();
          controls.endCall();
          DeskPRO_Window.TabBar.removeTabById(self.meta.tabId);
        });
      }
    });

    this.addEvent('closeTab', function(event) {
      if (controls.isCallActive()) {
        event.deskpro.cancelClose = true;
        confirmCloseOverlay.open();
      }
    }, this);
  }
});
