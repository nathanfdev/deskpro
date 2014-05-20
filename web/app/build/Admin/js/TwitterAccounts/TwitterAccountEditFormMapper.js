(function() {
  var __hasProp = {}.hasOwnProperty;

  define(['DeskPRO/Util/Util'], function(Util) {
    var Admin_TwitterAccounts_TwitterAccountEditFormMapper;
    return Admin_TwitterAccounts_TwitterAccountEditFormMapper = (function() {
      function Admin_TwitterAccounts_TwitterAccountEditFormMapper() {}


      /*
      			 *
       		 *
       */

      Admin_TwitterAccounts_TwitterAccountEditFormMapper.prototype.getFormFromModel = function(model) {
        var form, id, ids, _i, _len;
        form = {};
        form.id = model.twitter_account.id;
        form.verified = model.twitter_account.verified;
        form.user = {};
        form.user.profile_image_url = model.twitter_account.user.profile_image_url;
        form.user.name = model.twitter_account.user.name;
        form.user.screen_name = model.twitter_account.user.screen_name;
        form.user.agents = model.twitter_account.user.agents;
        form.agents = model.all_agents;
        form.selected_agents = {};
        ids = _.pluck(form.user.agents, 'id');
        for (_i = 0, _len = ids.length; _i < _len; _i++) {
          id = ids[_i];
          form.selected_agents[id] = true;
        }
        return form;
      };


      /*
      			 *
      			 *
       */

      Admin_TwitterAccounts_TwitterAccountEditFormMapper.prototype.applyFormToModel = function(model, formModel) {
        return model.id = formModel.id;
      };


      /*
      			 *
      			 *
       */

      Admin_TwitterAccounts_TwitterAccountEditFormMapper.prototype.getPostDataFromForm = function(formModel) {
        var agent, key, postData, value, _ref;
        postData = {};
        postData.id = formModel.id;
        postData.persons = [];
        _ref = formModel.selected_agents;
        for (key in _ref) {
          if (!__hasProp.call(_ref, key)) continue;
          value = _ref[key];
          if (value) {
            agent = _.findWhere(formModel.agents, {
              id: parseInt(key)
            });
            if (agent) {
              postData.persons.push(agent.id);
            }
          }
        }
        return postData;
      };

      return Admin_TwitterAccounts_TwitterAccountEditFormMapper;

    })();
  });

}).call(this);

//# sourceMappingURL=TwitterAccountEditFormMapper.js.map
