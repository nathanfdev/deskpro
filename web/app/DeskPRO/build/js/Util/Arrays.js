(function() {
  var __slice = [].slice;

  define(function() {
    var DeskPRO_Util_Arrays;
    DeskPRO_Util_Arrays = (function() {
      function DeskPRO_Util_Arrays() {}

      /*
        	# Analyze a flat array of categories that have structure defined like:
        	# - id: the unique ID
        	# - parent_id: The parent, or 0/null for no parent
        	# - title: The title of the category
        	#
        	# Returns a new flat array with additional information:
        	# - parent_ids: An array of parents
        	# - child_ids: An array of any chilcren
        	# - depth: How deep the category is in the structure
        	# - title_segs: An array of parent titles and this title (eg to generate a breadcrumb)
        	# - full_title: A string of all titles separated by a ' > '
        	#
        	# @return {Array}
      */


      DeskPRO_Util_Arrays.prototype.analyzeFlatCatStructure = function(cats) {
        var fnProc, ret;
        ret = [];
        fnProc = function(parent_id, parent_ids, title_segs) {
          var cat, child_ids, copy, doAdd, _i, _len, _results;
          if (parent_ids == null) {
            parent_ids = [];
          }
          if (title_segs == null) {
            title_segs = [];
          }
          child_ids = [];
          _results = [];
          for (_i = 0, _len = cats.length; _i < _len; _i++) {
            cat = cats[_i];
            doAdd = false;
            if (!parent_id && !cat.parent_id) {
              doAdd = true;
            } else if (parent_id && cat.parent_id === parent_id) {
              doAdd = true;
            }
            if (!doAdd) {
              continue;
            }
            copy = _.clone(cat);
            copy.parent_ids = parent_ids.slice(0);
            copy.title_segs = title_segs.slice(0);
            copy.depth = parent_ids.length;
            title_segs.push(copy.title);
            copy.full_title = title_segs.join(' > ');
            copy.title_segs = title_segs.slice(0);
            ret.push(copy);
            parent_ids.push(copy.id);
            copy.child_ids = fnProc(copy.id, parent_ids, title_segs);
            parent_ids.pop();
            title_segs.pop();
            _results.push(child_ids = _.union(child_ids, copy.child_ids));
          }
          return _results;
        };
        fnProc(null, [], []);
        return ret;
      };

      /*
        	# Pushes value on to array only if value does not already exist in array.
        	#
        	# @param  {Array} array
        	@ @param  mixed   value
        	# @return {Array}
      */


      DeskPRO_Util_Arrays.prototype.pushUnique = function(array, value) {
        if (array.indexOf(value) === -1) {
          array.push(value);
        }
        return array;
      };

      /*
      		# Pushes value on to array only if value does not already exist in array.
      		#
      		# @param  {Array} array
      		@ @param  mixed   value
      		# @return {Array}
      */


      DeskPRO_Util_Arrays.prototype.unshiftUnique = function(array, value) {
        if (array.indexOf(value) === -1) {
          array.push(value);
        }
        return array;
      };

      /*
      		# Append arrays to array
      		#
      		# @param {Array} array  The array to append on
      		# @param {Array} arrays... One or more arrays to add to array
      		# @return {Array}
      */


      DeskPRO_Util_Arrays.prototype.append = function() {
        var arr, array, arrays, v, _i, _j, _len, _len1;
        array = arguments[0], arrays = 2 <= arguments.length ? __slice.call(arguments, 1) : [];
        for (_i = 0, _len = arrays.length; _i < _len; _i++) {
          arr = arrays[_i];
          for (_j = 0, _len1 = arr.length; _j < _len1; _j++) {
            v = arr[_j];
            array.push(v);
          }
        }
        return array;
      };

      return DeskPRO_Util_Arrays;

    })();
    return new DeskPRO_Util_Arrays();
  });

}).call(this);

/*
//@ sourceMappingURL=Arrays.js.map
*/