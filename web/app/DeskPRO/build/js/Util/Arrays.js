(function() {
  define(function() {
    return {
      analyzeFlatCatStructure: function(cats) {
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
      }
    };
  });

}).call(this);

/*
//@ sourceMappingURL=Arrays.js.map
*/