/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/BaseListEdit',
  'Admin/TicketDeps/TicketDepFormMapper',
  'DeskPRO/Util/Arrays',
  'DeskPRO/Util/Util'
], function(
  BaseListEdit,
  TicketDepFormMapper,
  Arrays,
  Util
)  {
  let TicketDeps;
  return TicketDeps = (function() {
    TicketDeps = class TicketDeps extends BaseListEdit {
      static initClass() {
        this.$inject = ['Api', 'Api2', '$q'];
      }

      url() { return '/ticket_deps'; }

      resolveResponse(response) { return response.departments; }

      all(reload) {
        return super.all(reload, {with_perms: 1});
      }

      _doLoadList(params) {
        const deferred = this.$q.defer();
        params = params || {};

        // maybe should init query params as method argument
        this.Api.sendGet(this.url(), params).success( (data, status, headers, config) => {
          this.deps = data.departments;
          var proc = function(parent) {
            const list = [];

            const parent_id = parent ? parent.id : null;
            for (let d of Array.from(data.departments)) {
              if (d.parent_id === parent_id) {
                d.parent = parent;
                d.children = proc(d);
                list.push(d);
              }
            }

            return list;
          };

          const models = proc(null);
          return deferred.resolve(models);
        }
        , (data, status, headers, config) => deferred.reject());

        return deferred.promise;
      }


      /*
        * Get the form mapper
        *
        * @return {TicketDepFormMapper}
      */
      getFormMapper() {
        if (this.formMapper) { return this.formMapper; }
        this.formMapper = new TicketDepFormMapper();
        return this.formMapper;
      }


      /*
        * Get all info needed for an 'edit department' form
        *
        * @return {promise}
      */
      getEditDepartmentData(id) {
        let promise;
        if (id) {
          promise = this.Api.sendDataGet({
            depInfo:            `/ticket_deps/${id}`,
            agentsInfo:         '/agents',
            agentgroupsInfo:    '/agent_groups',
            usergroupsInfo:     '/user_groups',
            ticketAccountsInfo: '/email_accounts',
            defaultLayoutInfo:  '/ticket_layouts/default',
            customLayoutInfo:   `/ticket_layouts/${id}`,
            layoutStats:        "/ticket_layouts/stats"
          });
        } else {
          promise = this.Api.sendDataGet({
            agentsInfo:         '/agents',
            agentgroupsInfo:    '/agent_groups',
            usergroupsInfo:     '/user_groups',
            ticketAccountsInfo: '/email_accounts',
            defaultLayoutInfo:  '/ticket_layouts/default',
            layoutStats:        "/ticket_layouts/stats"
          });
        }

        const brandPromise = this.Api2.sendGet('brands');
        const deferred = this.$q.defer();

        const allPromise = this.$q.all([promise, this.loadList(), brandPromise]).then( result => {
          const brands = result[2].data.data;

          result = result[0].data;

          const data = {};

          if (result.depInfo) {
            data.dep      = result.depInfo.department;
            data.depPerms = result.depInfo.permissions;
          } else {
            data.dep = {};
            data.depPerms = {
              usergroup_ids: [],
              agentgroup_ids: [],
              agent_ids: []
            };
          }

          data.layout_info = result.layoutStats.layout_info;

          data.email_accounts = result.ticketAccountsInfo.email_accounts;

          data.dep_parent_list = this.listModels.slice(0);
          if (data.dep.id) {
            for (let idx = 0; idx < data.dep_parent_list.length; idx++) {
              const d = data.dep_parent_list[idx];
              if (d.id === data.dep.id) {
                data.dep_parent_list = Arrays.removeIndex(data.dep_parent_list, idx);
                break;
              }
            }
          }

          data.agents          = result.agentsInfo.agents;
          data.brands          = brands;
          data.agentgroups     = result.agentgroupsInfo.groups;
          data.usergroups      = result.usergroupsInfo.groups;

          const layouts = {
            default_layout:    result.defaultLayoutInfo.layout,
            custom_layout:     result.customLayoutInfo ? result.customLayoutInfo.layout : null,
            use_custom_layout: result.customLayoutInfo && !result.customLayoutInfo.is_default ? true : false
          };
        
          data.form = this.getFormMapper().getFormFromModel(
            data.dep,
            (result.depInfo != null ? result.depInfo.trigger : undefined) || {},
            layouts,
            data.depPerms,
            data.agents,
            data.brands,
            data.agentgroups,
            data.usergroups,
            data.email_accounts
          );
        
          return deferred.resolve(data);
        });

        return deferred.promise;
      }


      /*
       * Gets an option array of full-title departments.
       *
       * @param {Integer} exclude_id  Dont include this dep in the list
       * @return {Array}
       */
      getLeafOptionsArray(exclude_id) {

        const list = [];

        var proc = (coll, title_seg) =>

          (() => {
            const result = [];
            for (let d of Array.from(coll)) {
              if (exclude_id && (d.id === exclude_id)) { continue; }

              if (!title_seg) { title_seg = []; }

              title_seg.push(d.title);

              if (d.children && !Util.isEmpty(d.children)) {
                proc(d.children, title_seg);
              } else {
                list.push({
                  id: d.id,
                  title: title_seg.join(" > ")
                });
              }

              result.push(title_seg.pop());
            }
            return result;
          })()
        ;

        proc(this.listModels);

        return list;
      }

      /*
       * Remove a model from the list by ID.
       *
       * @return {Object/null} The removed object or null if object could not be found
       */
      removeListModelById(id) {

        let idx, model;
        if (!this.isListLoaded) { return; }
        super.removeListModelById(id);

        if (!this.isListLoaded) { return; }

        let removeIdx = null;

        for (idx = 0; idx < this.deps.length; idx++) {
          model = this.deps[idx];
          if (model[this.idProp] === id) {
            removeIdx = idx;
            break;
          }
        }

        let result = null;

        if (removeIdx !== null) {
          result = this.deps.splice(removeIdx, 1);
          result = result[0];
        }

        // Remove from children arrays

        for (model of Array.from(this.listModels)) {
          if (!(model.children != null ? model.children.length : undefined)) { continue; }

          removeIdx = null;

          for (idx = 0; idx < model.children.length; idx++) {
            const subModel = model.children[idx];
            if (subModel.id === id) {
              removeIdx = idx;
              break;
            }
          }

          if (Util.isNumber(removeIdx)) {
            model.children.splice(removeIdx, 1);
          }
        }

        return result;
      }

      /*
       * Remove a department
       *
       * @param {Integer} id Department id
       * @param {Integer} move_to - id to which we want to move department data
       * @return {promise}
       */
      deleteDepartmentById(id, move_to) {
        const promise = this.Api.sendDelete(`/ticket_deps/${id}`, {
          move_to
        }).success(() => {
          return this.removeListModelById(id);
        });

        return promise;
      }


      /*
      * Save display orders
        *
        * @param {Array} orders An array of ids in order
        * @return {promise}
      */
      saveDisplayOrders(orders) {
        const postData = {display_orders: []};

        for (let order = 0; order < orders.length; order++) {
          const id = orders[order];
          const d = this.findListModelById(id);
          if (d) {
            d.display_order = order;
            postData.display_orders.push(id);
          }
        }

        const promise = this.Api.sendPostJson('/ticket_deps/display_order', postData);
        return promise;
      }


      /*
        * Saves a form model and applies the form model to the dep model
        * once finished.
        *
        * @param {Object} dep The dep model
        * @param {Object} formModel  The model representing the form
        * @return {promise}
      */
      saveFormModel(dep, formModel) {
        let promise;
        const mapper = this.getFormMapper();
        const postData = mapper.getPostDataFromForm(formModel);

        if (dep.id) {
          promise = this.Api.sendPostJson(`/ticket_deps/${dep.id}`, postData);
        } else {
          promise = this.Api.sendPutJson('/ticket_deps', postData).success( data => dep.id = data.id);
        }

        promise.success(() => {
          mapper.applyFormToModel(dep, formModel);
          return this.mergeDataModel(dep);
        });

        return promise;
      }
    };
    TicketDeps.initClass();
    return TicketDeps;
  })();
});