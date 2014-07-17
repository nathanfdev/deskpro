define [
	'Admin/Main/DataService/BaseModel',
], (
	BaseModel,
)  ->
	class Bans extends BaseModel

		url: ->
			'/labels/settings'
