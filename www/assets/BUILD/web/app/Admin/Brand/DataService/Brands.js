define([
	'Admin/Main/DataService/Base',
	'Admin/Main/Model/Base',
	'Admin/Main/Collection/OrderedDictionary'
], function(Admin_Main_DataService_Base,
	Admin_Main_Model_Base,
	Admin_Main_Collection_OrderedDictionary)  {
	class Admin_Brand_DataService_Brands extends Admin_Main_DataService_Base {
		constructor(em, Api, $q) {
			super(em);
			this.$q = $q;
			this.Api = Api;

			this.loadListPromise = null;
			this.recs = new Admin_Main_Collection_OrderedDictionary();
		}

		/**
		* Loads list of brands
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

			this.Api.sendGet('/brands').success((data, status, headers, config) => {
				this._setListData(data.brands);
				return deferred.resolve(this.recs);
			}
			, (data, status, headers, config) => deferred.reject());

			this.loadListPromise = deferred.promise;

			return this.loadListPromise;
		}

		remove(id) {
			this.recs.remove(id);
			return this.em.removeById('brand', 'id');
		}

		_setListData(raw_recs) {
			return (() => {
				const result = [];
				for (let rec of Array.from(raw_recs)) {
					const model = this.em.createEntity('brand', 'id', rec);
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
		updateModel(brand) {
			const new_model = this.em.createEntity('brand', 'id', brand);
			this.recs.set(new_model.id, new_model);
			return new_model;
		}

		/*
		* Adds a new model to the existing list (eg was just created)
		*
		* @return {Admin_Main_Model_Base}
		*/
		addToList(rec) {
			let model;
			if (!rec._is_model) {
				model = this.em.createEntity('brand', 'id', rec);
			} else {
				model = this.em.add(rec, true);
			}

			this.recs.set(model.id, model);
			return model;
		}
	}

	return Admin_Brand_DataService_Brands;
});
