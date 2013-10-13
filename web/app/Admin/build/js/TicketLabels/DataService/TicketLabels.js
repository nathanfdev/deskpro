(function() {
  define(['Admin/Main/DataService/Base', 'Admin/Main/Model/Base', 'Admin/Main/Collection/OrderedDictionary'], function(Admin_Main_DataService_Base, Admin_Main_Model_Base, Admin_Main_Collection_OrderedDictionary) {
    var Admin_TicketLabels_DataService_TicketLabels;
    return Admin_TicketLabels_DataService_TicketLabels = (function() {
      function Admin_TicketLabels_DataService_TicketLabels(Api, $q) {
        this.$q = $q;
        this.Api = Api;
        this.loadListPromise = null;
        this.recs = [];
      }

      /**
      		* Loads list of labels
        	*
        	* @return {Promise}
      */


      Admin_TicketLabels_DataService_TicketLabels.prototype.loadList = function(reload) {
        var deferred, http_def,
          _this = this;
        if (this.loadListPromise) {
          return this.loadListPromise;
        }
        deferred = this.$q.defer();
        if (!reload && this.recs.length) {
          deferred.resolve(this.recs);
          return deferred.promise;
        }
        http_def = this.Api.sendGet('/ticket_labels').success(function(data, status, headers, config) {
          _this.recs = data.labels;
          return deferred.resolve(_this.recs);
        }, function(data, status, headers, config) {
          return deferred.reject();
        });
        this.loadListPromise = deferred.promise;
        return this.loadListPromise;
      };

      Admin_TicketLabels_DataService_TicketLabels.prototype.getObjectByLabel = function(label) {
        return _.findWhere(this.recs, {
          'label': label
        });
      };

      Admin_TicketLabels_DataService_TicketLabels.prototype.remove = function(label_object) {
        var i;
        i = this.recs.indexOf(label_object);
        if (i > -1) {
          return this.recs.splice(i, 1);
        }
      };

      Admin_TicketLabels_DataService_TicketLabels.prototype.updateModel = function(label_object) {
        var i;
        i = this.recs.indexOf(label_object);
        if (i > -1) {
          this.recs[i] = label_object;
        } else {
          this.recs.push(label_object);
        }
        return label_object;
      };

      Admin_TicketLabels_DataService_TicketLabels.prototype.addToList = function(label_object) {
        return this.updateModel(label_object);
      };

      return Admin_TicketLabels_DataService_TicketLabels;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=TicketLabels.js.map
*/