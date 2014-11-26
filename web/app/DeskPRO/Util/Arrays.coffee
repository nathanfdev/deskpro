define ->
  class Arrays
    ###
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
    ###
    analyzeFlatCatStructure: (cats) ->
      ret = []
      fnProc = (parent_id, parent_ids = [], title_segs = []) ->
        child_ids = []
        for cat in cats
          doAdd = false
          if not parent_id and not cat.parent_id
            doAdd = true
          else if parent_id and cat.parent_id == parent_id
            doAdd = true

          if not doAdd then continue

          copy = _.clone(cat)
          copy.parent_ids = parent_ids.slice(0)
          copy.title_segs = title_segs.slice(0)
          copy.depth      = parent_ids.length

          title_segs.push(copy.title)
          copy.full_title = title_segs.join(' > ')
          copy.title_segs = title_segs.slice(0)

          ret.push(copy)
          parent_ids.push(copy.id)
          copy.child_ids = fnProc(copy.id, parent_ids, title_segs)
          parent_ids.pop()
          title_segs.pop()

          child_ids = _.union(child_ids, copy.child_ids)

      fnProc(null, [], [])

      return ret

    ###
      # Pushes value on to array only if value does not already exist in array.
      #
      # @param  {Array} array
      @ @param  mixed   value
      # @return {Array}
    ###
    pushUnique: (array, value) ->
      if array.indexOf(value) == -1
        array.push(value)

      return array


    ###
    # Pushes value on to array only if value does not already exist in array.
    #
    # @param  {Array} array
    @ @param  mixed   value
    # @return {Array}
    ###
    unshiftUnique: (array, value) ->
      if array.indexOf(value) == -1
        array.push(value)

      return array


    ###
    # Append arrays to array
    #
    # @param {Array} array  The array to append on
    # @param {Array} arrays... One or more arrays to add to array
    # @return {Array}
    ###
    append: (array, arrays...) ->
      for arr in arrays
        for v in arr
          array.push(v)

      return array


    ###
    # Insert a value into an array a specific location.
      # Modifies the array in place.
      #
      # @param {Array} array
      # @param {mixed} value
      # @param {Integer} index
      ###
    insertAtIndex: (array, value, index) ->
      array.splice(index, 0, value)
      return array


    ###
    # Remove all occurances of removeVal in array.
    # Modifies the array in-place.
    #
    # @param {Array} array
    # @param {Integer} idx
      # @param {Integer} limit
    # @return {Array}
    ###
    removeValue: (array, removeVal, limit) ->
      count = 0
      while (idx = array.indexOf(removeVal)) != -1
        array.splice(idx, 1)
        ++count
        return array if limit and count >= limit

      return array


    ###
      # Remove all items of an array that match a fn.
      # Modifies the array in-place.
      #
      # @param {Array} array
      # @param {Function} fn
      # @param {Integer} limit
      # @return {Array}
      ###
    findAndRemove: (array, fn, limit) ->
      count = 0
      while (idx = this.findIndex(array, fn)) != -1
        array.splice(idx, 1)
        ++count
        return array if limit and count >= limit

      return array


    ###
      # Remove a specific element of an array.
      # Modifies the array in-place.
      #
      # @param {Array} array
      # @param {Integer} idx
      # @return {Array}
    ###
    removeIndex: (array, idx) ->
      array.splice(idx, 1)
      return array


    ###
    # Find the first value of an array that matches a fn
      #
      # @param {Array} array
      # @param {Function} fn
      # @return {mixed}
    ###
    find: (array, fn) ->
      for v, i in array
        if (fn(v, i, array))
          return v
      return null


    ###
    # Find the first index of an array item that matches a fn
      #
      # @param {Array} array
      # @param {Function} fn
      # @return {Integer}
    ###
    findIndex: (array, fn) ->
      for v, i in array
        if (fn(v, i, array))
          return i
      return -1


    ###
    # Sets the values of an array. This is different from simply assigning
      # a variable because this will modify `array` "in place".
      #
      # @param {Array} array
      # @param {Array} values
      # @return array
    ###
    setTo: (array, values) ->
      array.length = 0
      for v in values
        array.push(v)

      return array

  return new Arrays()