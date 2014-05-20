(function() {
  define(function() {
    var Admin_Main_Directive_DpDevBar;
    Admin_Main_Directive_DpDevBar = [
      function() {
        return {
          restrict: 'E',
          replace: true,
          template: "<div style=\"position: relative\">\n	<div id=\"dp_dev_bar_btn\">\n		<button><i class=\"fa fa-flask\"></i> Devbar</button>\n	</div>\n	<div id=\"dp_dev_bar\" style=\"display:none;\">\n		<button class=\"trigger_reloadcss\"><i class=\"fa fa-eye\"></i> Reload CSS</button>\n		<button class=\"trigger_reloadpage\"><i class=\"fa fa-refresh\"></i> Reload Page</button>\n		<button class=\"trigger_close\"><i class=\"fa fa-times-circle\"></i></button>\n	</div>\n</div>",
          link: function(scope, element, attrs) {
            element.find('#dp_dev_bar_btn').on('click', function(ev) {
              element.find('#dp_dev_bar_btn').hide();
              return element.find('#dp_dev_bar').show();
            });
            element.find('.trigger_close').on('click', function(ev) {
              element.find('#dp_dev_bar_btn').show();
              return element.find('#dp_dev_bar').hide();
            });
            element.find('.trigger_reloadcss').on('click', function(ev) {
              var qs;
              ev.preventDefault();
              qs = '?reload=' + new Date().getTime();
              return $('link[rel="stylesheet"]').each(function() {
                return this.href = this.href.replace(/\?.*|$/, qs);
              });
            });
            return element.find('.trigger_reloadpage').on('click', function(ev) {
              ev.preventDefault();
              return window.location.reload(false);
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpDevBar;
  });

}).call(this);

//# sourceMappingURL=DpDevBar.js.map
