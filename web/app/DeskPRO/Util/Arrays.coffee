define ->
	{
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

					ret.push(copy)
					parent_ids.push(copy.id)
					copy.child_ids = fnProc(copy.id, parent_ids, title_segs)
					parent_ids.pop()
					title_segs.pop()

					child_ids = _.union(child_ids, copy.child_ids)

			fnProc(null, [], [])

			return ret
	}