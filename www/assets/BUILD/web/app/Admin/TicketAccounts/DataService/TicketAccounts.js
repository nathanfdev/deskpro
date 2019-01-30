/*
 * decaffeinate suggestions:
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS205: Consider reworking code to avoid use of IIFEs
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/DataService/Base',
  'Admin/Main/Model/Base',
  'Admin/Main/Collection/OrderedDictionary'
], function(
  Admin_Main_DataService_Base,
  Admin_Main_Model_Base,
  Admin_Main_Collection_OrderedDictionary
)  {
  let Admin_TicketAccounts_DataService_TicketAccounts;
  return (Admin_TicketAccounts_DataService_TicketAccounts = class Admin_TicketAccounts_DataService_TicketAccounts extends Admin_Main_DataService_Base {
    constructor(em, Api, $q) {
      super(em);
      this.$q   = $q;
      this.Api  = Api;

      this.loadListPromise = null;
      this.recs = new Admin_Main_Collection_OrderedDictionary();

      this.recs.orderFn = function(a, b) {
        const cmpa = a.address;
        const cmpb = b.address;
        if (cmpa < cmpb) { return -1; } else { return 1; }
      };
    }

    /**
    * Loads list of accounts
      *
      * @return {Promise}
    */
    loadList(reload) {

      if (this.loadListPromise) {
        return this.loadListPromise;
      }

      const deferred = this.$q.defer();
      if (!reload && this.recs.count()) {
        deferred.resolve(this.recs);
        return deferred.promise;
      }

      const http_def = this.Api.sendGet('/email_accounts').success( (data, status, headers, config) => {
        this._setListData(data.email_accounts);
        return deferred.resolve(this.recs);
      }
      , (data, status, headers, config) => deferred.reject());

      this.loadListPromise = deferred.promise;

      return this.loadListPromise;
    }

    remove(id) {
      this.recs.remove(id);
      return this.em.removeById('ticket_account', 'id');
    }

    _setListData(raw_recs) {
      return (() => {
        const result = [];
        for (let rec of Array.from(raw_recs)) {
          const model = this.em.createEntity('ticket_account', 'id', rec);
          model.retain();
          result.push(this.recs.set(model.id, model));
        }
        return result;
      })();
    }

    /*
      * Updates the first-class model (title, etc)
      * with account provided. Or adds it to the list if it doesnt exist.
      */
    updateModel(account) {
      const new_model = this.em.createEntity('ticket_account', 'id', account);
      this.recs.set(new_model.id, new_model);
      return new_model;
    }

    /**
    * Adds a new model to the existing list (eg was just created)
      *
      * @return {Admin_Main_Model_Base}
    */
    addToList(rec) {
      let model;
      if (!rec._is_model) {
        model = this.em.createEntity('ticket_account', 'id', dep);
      } else {
        model = this.em.add(rec, true);
      }

      this.recs.set(model.id, model);
      return model;
    }
  });
});