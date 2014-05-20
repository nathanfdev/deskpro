define ['Admin/Labels/Base/Ctrl/Edit'], (Admin_Labels_Base_Ctrl_Edit) ->
	class Admin_Labels_Feedback_Ctrl_Edit extends Admin_Labels_Base_Ctrl_Edit
		@CTRL_ID = 'Admin_Labels_Feedback_Ctrl_Edit'
		@CTRL_AS = 'LabelsEdit'
		@DEPS = ['em', '$stateParams', '$rootScope', 'LabelManager']

		init: ->
			super()
			@api_endpoint = '/feedback_labels'
			@ng_route     = 'portal.feedback_labels'
			@typename     = 'labels_feedback'

	Admin_Labels_Feedback_Ctrl_Edit.EXPORT_CTRL()