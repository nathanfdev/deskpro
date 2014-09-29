(function() {
  var __hasProp = {}.hasOwnProperty;

  define(function() {
    var Admin_TicketMacros_MacroEditFormMapper;
    return Admin_TicketMacros_MacroEditFormMapper = (function() {
      function Admin_TicketMacros_MacroEditFormMapper() {}

      Admin_TicketMacros_MacroEditFormMapper.prototype.getFormFromModel = function(macroModel) {
        var form, _ref;
        form = {};
        form.title = macroModel.title || '';
        form.is_global = macroModel.is_global;
        form.person_id = "0";
        form.actions = ((_ref = macroModel.actions) != null ? _ref.actions : void 0) || {};
        if (macroModel.person) {
          form.person_id = macroModel.person.id + "";
        }
        return form;
      };

      Admin_TicketMacros_MacroEditFormMapper.prototype.applyFormToModel = function(macroModel, formModel) {
        return macroModel.title = formModel.title;
      };

      Admin_TicketMacros_MacroEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var id, postData, row, _ref;
        postData = {};
        postData.title = formModel.title;
        if (formModel.is_global) {
          postData.is_global = true;
          postData.person_id = null;
        } else {
          postData.is_global = false;
          postData.person_id = formModel.person_id;
        }
        postData.actions = [];
        _ref = formModel.actions;
        for (id in _ref) {
          if (!__hasProp.call(_ref, id)) continue;
          row = _ref[id];
          postData.actions.push(row);
        }
        return postData;
      };

      return Admin_TicketMacros_MacroEditFormMapper;

    })();
  });

}).call(this);

//# sourceMappingURL=MacroEditFormMapper.js.map
