(function() {
  define(function() {

    /*
        *
     */
    var DeskPRO_Directive_DpTicketQuickActions;
    return DeskPRO_Directive_DpTicketQuickActions = [
      '$parse', 'formatTimestampAgoFilter', function($parse, formatTimestampAgo) {
        var $widget, elements;
        $widget = $('<div class="dp-stickytip"> <div class="previewtext-row"> <cite> <img /> <span>person name</span> <span>action (created/replied/wrote note)</span> <span>Time ago</span> </cite> <br /> <span>preview text</span> </div> <ul class="previewtext-row actions"> <li><a href="#">Assign Me</a></li> <li><a href="#">Assign Agent</a></li> <li><a href="#">Assign Team</a></li> <li><a href="#">Set Awaiting User</a></li> <li><a href="#">Set Resolved</a></li> </ul> </div>').hide().appendTo('body');
        elements = {
          icon: $widget.find('cite > img:eq(0)').css('vertical-align', 'bottom')[0],
          name: $widget.find('cite > span:eq(0)')[0],
          status: $widget.find('cite > span:eq(1)')[0],
          time: $widget.find('cite > span:eq(2)')[0],
          msg: $widget.find('div > span:eq(0)').dotdotdot({
            elipsis: '...',
            wrap: 'word',
            height: 62
          })
        };
        return {
          restrict: 'A',
          scope: false,
          replace: false,
          link: function($scope, $el, attr) {
            var ticket;
            ticket = $parse(attr.dpTicketQuickActions)($scope);
            if (!ticket || !ticket.previews.length) {
              return;
            }
            $el.on('mouseover', (function(_this) {
              return function() {
                $widget.show().css({
                  left: $el.offset().left,
                  top: $el.offset().top + $el.height()
                });
                DP_DEBUG && console.time('Ticket quick actions render');
                elements.icon.src = ticket.previews[0].person.picture_url_16;
                elements.name.innerText = ticket.previews[0].person.display_name;
                elements.status.innerText = ticket.previews[0].message.status;
                elements.time.innerText = formatTimestampAgo(ticket.previews[0].message.date_created_ts);
                elements.msg.text(ticket.previews[0].message.preview_text).trigger('update');
                return DP_DEBUG && console.timeEnd('Ticket quick actions render');
              };
            })(this));
            return $el.on('mouseleave', (function(_this) {
              return function() {
                return $widget.hide();
              };
            })(this));
          }
        };
      }
    ];
  });

}).call(this);

//# sourceMappingURL=DpTicketQuickActions.js.map
