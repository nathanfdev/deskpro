(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/DataService/Base', 'Admin/Main/Model/Base', 'Admin/Main/Collection/OrderedDictionary'], function(Admin_Main_DataService_Base, Admin_Main_Model_Base, Admin_Main_Collection_OrderedDictionary) {
    var Admin_ChannelFacebook_DataService_FacebookPages;
    return Admin_ChannelFacebook_DataService_FacebookPages = (function(_super) {
      __extends(Admin_ChannelFacebook_DataService_FacebookPages, _super);

      function Admin_ChannelFacebook_DataService_FacebookPages(em, Api, $q) {
        Admin_ChannelFacebook_DataService_FacebookPages.__super__.constructor.call(this, em);
        this.$q = $q;
        this.Api = Api;
        this.loadListPromise = null;
        this.recs = new Admin_Main_Collection_OrderedDictionary();
      }


      /**
      		* Loads list of accounts
      		*
      		* @return {Promise}
       */

      Admin_ChannelFacebook_DataService_FacebookPages.prototype.loadList = function(reload) {
        var deferred, http_def;
        if (this.loadListPromise) {
          return this.loadListPromise;
        }
        deferred = this.$q.defer();
        if (!reload && this.recs.count()) {
          deferred.resolve(this.recs);
          return deferred.promise;
        }
        http_def = this.Api.sendGet('/channel/facebook/pages').success((function(_this) {
          return function(data, status, headers, config) {
            _this._setListData(data.facebook_pages);
            return deferred.resolve(_this.recs);
          };
        })(this), function(data, status, headers, config) {
          return deferred.reject();
        });
        this.loadListPromise = deferred.promise;
        return this.loadListPromise;
      };

      Admin_ChannelFacebook_DataService_FacebookPages.prototype.remove = function(id) {
        this.recs.remove(id);
        return this.em.removeById('facebook_page', 'id');
      };

      Admin_ChannelFacebook_DataService_FacebookPages.prototype._setListData = function(raw_recs) {
        var model, rec, _i, _len, _results;
        _results = [];
        for (_i = 0, _len = raw_recs.length; _i < _len; _i++) {
          rec = raw_recs[_i];
          model = this.em.createEntity('facebook_page', 'id', rec);
          model.retain();
          _results.push(this.recs.set(model.id, model));
        }
        return _results;
      };

      Admin_ChannelFacebook_DataService_FacebookPages.prototype.checkExistsByGraphId = function(graph_id) {
        var found_it;
        found_it = false;
        this.recs.forEach(function(id, rec) {
          if (rec.graph_id === graph_id) {
            return found_it = true;
          }
        });
        return found_it;
      };


      /*
      		 * Updates the first-class model (title, etc)
      		 * with page provided. Or adds it to the list if it doesnt exist.
       */

      Admin_ChannelFacebook_DataService_FacebookPages.prototype.updateModel = function(page) {
        var new_model;
        new_model = this.em.createEntity('facebook_page', 'id', page);
        this.recs.set(new_model.id, new_model);
        return new_model;
      };


      /*
      		 * Adds a new model to the existing list (eg was just created)
      		 *
      		 * @return {Admin_Main_Model_Base}
       */

      Admin_ChannelFacebook_DataService_FacebookPages.prototype.addToList = function(rec) {
        var model;
        if (!rec._is_model) {
          model = this.em.createEntity('facebook_page', 'id', rec);
        } else {
          model = this.em.add(rec, true);
        }
        this.recs.set(model.id, model);
        return model;
      };

      return Admin_ChannelFacebook_DataService_FacebookPages;

    })(Admin_Main_DataService_Base);
  });

}).call(this);

//# sourceMappingURL=FacebookPages.js.map
