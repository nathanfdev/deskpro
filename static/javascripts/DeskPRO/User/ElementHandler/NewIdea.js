Orb.createNamespace('DeskPRO.User.ElementHandler');

DeskPRO.User.ElementHandler.NewIdea = new Orb.Class({

	Extends: DeskPRO.User.ElementHandler.ElementHandlerAbstract,

	init: function() {
		this._initSuggestionsBox();
		this._initLoginForm();
	},

	//#########################################################################
	//# Suggestions
	//#########################################################################

	_initSuggestionsBox: function() {
		this.inlineSuggestions = new DeskPRO.User.InlineSuggestions({
			elementWrapper: this.el,
			titleText: '#idea_title',
			contentText: '#idea_content'
			//onResolved: this.setTicketSolvedAjax.bind(this),
			//onResolvedRedirect: this.setTicketSolvedRedirect.bind(this),
			//onNotResolved: this.setTicketUnsolvedAjax.bind(this)
		});
	},

	//#########################################################################
	// In-page login form
	//#########################################################################

	_initLoginForm: function(context) {
		this.inlineLogin = new DeskPRO.User.InlineLoginForm({
			context: this.el
		});
	}
});
