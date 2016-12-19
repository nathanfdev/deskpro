define(function() {
  return {
    init: function() {
      var el = this.getFragmentElement();
      var $ticket = this.getTicketData();
      var $app = this.getApp();
      var $http = $app.getHttp();

      var clickUpdate = function(me) {
        var ip = me.data('ip');
        if (!ip) return;

        var targetEl = me.parent().find('.dp_hostname_val').first();
        if (!targetEl[0]) return;

        var old = targetEl.text();
        targetEl.text('...');

        $http.get($app.getRequestHandlerUrl('agent', 'refresh-hostname', { ticket_id: $ticket.id, ip: ip }), {
          responseType: 'json'
        }).success(function(data) {
          if (data.nochange) {
            targetEl.text(old);
            return;
          }

          targetEl.text(data.hostname || 'Unknown');
        });
      }

      el.on('click', '.dp_hostname_reload, .dp_hostname_reload_row', function(ev) {
        ev.preventDefault();
        clickUpdate($(this));
      });

      el.find('.dp-stickytip').find('.dp_hostname_reload').on('click', function(ev) {
        ev.preventDefault();
        clickUpdate($(this));
      })
    }
  };
})