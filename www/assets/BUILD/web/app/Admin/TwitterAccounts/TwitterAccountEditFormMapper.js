// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Util',
  'underscore'
], function(
  Util,
  _
) {
  let Admin_TwitterAccounts_TwitterAccountEditFormMapper;
  return (Admin_TwitterAccounts_TwitterAccountEditFormMapper = class Admin_TwitterAccounts_TwitterAccountEditFormMapper {

    /*
      *
    *
    */

    getFormFromModel(model) {

      const form = {};

      form.id = model.twitter_account.id;
      form.verified = model.twitter_account.verified;

      form.user = {};
      form.user.profile_image_url = model.twitter_account.user.profile_image_url;
      form.user.name = model.twitter_account.user.name;
      form.user.screen_name = model.twitter_account.user.screen_name;
      form.user.agents = model.twitter_account.user.agents;

      form.agents = model.all_agents;

      form.selected_agents = {};

      const ids = _.pluck(form.user.agents, 'id');

      for (let id of Array.from(ids)) {
        form.selected_agents[id] = true;
      }

      return form;
    }

    /*
      *
      *
    */

    applyFormToModel(model, formModel) {

      return model.id = formModel.id;
    }

    /*
      *
      *
    */

    getPostDataFromForm(formModel) {

      const postData = {};

      postData.id = formModel.id;

      // instead of agents, resulting request should include persons

      postData.persons = [];

      for (let key of Object.keys(formModel.selected_agents || {})) {
        const value = formModel.selected_agents[key];
        if (value) {
          const agent = _.findWhere(formModel.agents, {id: parseInt(key)});
          if (agent) { postData.persons.push(agent.id); }
        }
      }

      return postData;
    }
  });
});