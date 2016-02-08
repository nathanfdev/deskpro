define([
    'deskpro_custom_ticketer/TicketTab',
    'deskpro_custom_ticketer/OrgTab',
    'deskpro_custom_ticketer/Ctrl/OrgExtraPanelCtrl',
    'deskpro_custom_ticketer/Ctrl/OrgPanelCtrl'
], function(
    TicketTab,
    OrgTab,
    OrgExtraPanelCtrl,
    OrgPanelCtrl
) {
    return {
        init: function() {
            $('<link rel="stylesheet" type="text/css" />')
                .attr('href', this.getResourcePath('style.css'))
                .appendTo('body');

            this.register('ticket', TicketTab);
            this.register('org', OrgTab);
            this.registerWidgetTab("org", "@members.extra_tabs", "{{tab.title}}", "Org/org-extra.html", OrgExtraPanelCtrl);
            this.registerWidgetTab("org", "@members.extra_tabs", "{{tab.title}}", "Org/org-panel.html", OrgPanelCtrl);

            this.hackTicketClass();
            this.hackOrgClass();

            // a global function
            var operatorFid = this.getSetting('org_is_operator_fid');
            window.DP_CHECK_ORG_IS_OPERATOR = function(org) {
                var val = (org['field' + operatorFid] || false);
                if (val !== true && val !== false) {
                    val = (''+val).toLowerCase();
                }
                val = val === 'true' || val === 'yes' || val === 'on' || val === 'checked';
                return val;
            }
        },

        // Hack the message gear menu
        hackTicketClass: function() {
            var TicketClass = DeskPRO.Agent.PageFragment.Page.Ticket;
            var orig = TicketClass.prototype._initMessageActionsMenu;
            TicketClass.prototype._initMessageActionsMenu = function() {
                orig.apply(this);
                var menuElement = $('.ticket-message-edit-menu', this.wrapper);
                this.messageActionsMenu.addEvent('beforeMenuOpened', function(info) {
                    var message = $(info.menu.getOpenTriggerElement()).closest('article.message');
                    if (!message.hasClass('note-message')) {
                        menuElement.find('li.set-as-message').hide();
                        menuElement.find('li.set-as-note').hide();
                        menuElement.find('li.delete-link').hide();
                        menuElement.find('li.delete-attachments-link').hide();
                        menuElement.find('li[data-option-id="edit"]').hide();
                    }
                }, this);
            };
        },

        // This hacks the Org view to add a new tab placeholder
        hackOrgClass: function() {
            var OrgClass = DeskPRO.Agent.PageFragment.Page.Organization;
            var orig = OrgClass.prototype.initPage;
            OrgClass.prototype.initPage = function(el) {
                var baseId = this.meta.baseId;
                el.find('.profile-box-container.tabbed').addClass('dp-simpletab-container');
                el.find('#'+baseId+'_members_tab').closest('section').append('<div id="'+baseId+'_members_extra_tabs" class="dp-app-context-container as-default-hidden" data-location-name="members.extra_tabs"></div>');
                orig.apply(this, [el]);
            }
        }
    };
});
