(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['DeskPRO/Util/Util'], function(Util) {
    var UserGroupEditFormMapper;
    return UserGroupEditFormMapper = (function() {
      function UserGroupEditFormMapper() {}

      /*
      			#
       		#
      */


      UserGroupEditFormMapper.prototype.getFormFromModel = function(model) {
        var form, key, permission, _i, _len, _ref;
        form = {};
        form.id = model.user_group.id;
        form.title = model.user_group.title;
        form.note = model.user_group.note;
        form.is_enabled = model.user_group.is_enabled;
        form.permissions = {};
        _ref = model.user_group.permissions;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          permission = _ref[_i];
          key = permission.name.replace('.', '_');
          if (Util.isBlank(permission.value)) {
            form.permissions[key] = false;
          } else {
            form.permissions[key] = true;
          }
        }
        return form;
      };

      /*
      			#
      			#
      */


      UserGroupEditFormMapper.prototype.applyFormToModel = function(model, formModel) {
        return model.title = formModel.title;
      };

      /*
      			#
      			#
      */


      UserGroupEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var permission, postData, value, _ref;
        postData = {};
        postData.id = formModel.id;
        postData.title = formModel.title;
        postData.note = formModel.note;
        postData.is_enabled = formModel.is_enabled;
        postData.permissions = [];
        console.log(formModel.permissions);
        _ref = formModel.permissions;
        for (permission in _ref) {
          if (!__hasProp.call(_ref, permission)) continue;
          value = _ref[permission];
          postData.permissions.push({
            usergroup_id: formModel.id,
            name: permission.replace('_', '.'),
            value: (value ? 1 : 0),
            person_id: null
          });
        }
        return postData;
      };

      return UserGroupEditFormMapper;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=UserGroupEditFormMapper.js.map
*/