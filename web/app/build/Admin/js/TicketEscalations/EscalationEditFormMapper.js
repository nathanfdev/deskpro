(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['DeskPRO/Util/Util'], function(Util) {
    var Admin_TicketEscalations_EscalationEditFormMapper;
    return Admin_TicketEscalations_EscalationEditFormMapper = (function() {
      function Admin_TicketEscalations_EscalationEditFormMapper() {}

      Admin_TicketEscalations_EscalationEditFormMapper.prototype.getFormFromModel = function(escModel) {
        var action, form, rowId, term, _i, _j, _len, _len1, _ref, _ref1, _ref2, _ref3, _ref4, _ref5, _ref6;
        form = {};
        form.title = escModel.title || '';
        form.event_trigger = escModel.event_trigger || 'time.open';
        form.event_trigger_time = escModel.event_trigger_time || 3600;
        form.actions = ((_ref = escModel.actions) != null ? _ref.actions : void 0) || {};
        form.terms = {};
        form.actions = {};
        if ((_ref1 = escModel.terms) != null ? (_ref2 = _ref1.terms) != null ? _ref2.length : void 0 : void 0) {
          _ref3 = escModel.terms.terms;
          for (_i = 0, _len = _ref3.length; _i < _len; _i++) {
            term = _ref3[_i];
            rowId = _.uniqueId('term');
            form.terms[rowId] = term;
          }
        }
        if ((_ref4 = escModel.actions) != null ? (_ref5 = _ref4.actions) != null ? _ref5.length : void 0 : void 0) {
          _ref6 = escModel.actions.actions;
          for (_j = 0, _len1 = _ref6.length; _j < _len1; _j++) {
            action = _ref6[_j];
            rowId = _.uniqueId('action');
            form.actions[rowId] = action;
          }
        }
        return form;
      };

      Admin_TicketEscalations_EscalationEditFormMapper.prototype.applyFormToModel = function(escModel, formModel) {
        return escModel.title = formModel.title;
      };

      Admin_TicketEscalations_EscalationEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var crit, crit_set, id, postData, row, _, _ref, _ref1;
        postData = {};
        postData.title = formModel.title;
        postData.event_trigger = formModel.event_trigger;
        postData.event_trigger_time = formModel.event_trigger_time;
        postData.actions = [];
        _ref = formModel.actions;
        for (id in _ref) {
          if (!__hasProp.call(_ref, id)) continue;
          row = _ref[id];
          postData.actions.push(row);
        }
        postData.terms = [];
        _ref1 = formModel.terms_set;
        for (_ in _ref1) {
          if (!__hasProp.call(_ref1, _)) continue;
          crit_set = _ref1[_];
          for (_ in crit_set) {
            if (!__hasProp.call(crit_set, _)) continue;
            crit = crit_set[_];
            if (crit.type) {
              postData.terms.push(crit);
            }
          }
          if (postData.terms.length) {
            break;
          }
        }
        return postData;
      };

      return Admin_TicketEscalations_EscalationEditFormMapper;

    })();
  });

}).call(this);

//# sourceMappingURL=EscalationEditFormMapper.js.map
