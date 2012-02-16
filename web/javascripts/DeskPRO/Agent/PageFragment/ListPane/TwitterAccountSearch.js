Orb.createNamespace('DeskPRO.Agent.PageFragment.ListPane');

DeskPRO.Agent.PageFragment.ListPane.TwitterAccountSearch = new Orb.Class({
    Extends: DeskPRO.Agent.PageFragment.ListPane.Basic,

    initPage: function(el) {
        this.wrapper = $(el);

        this.searchBox = this.wrapper.find('input.twitter-search');

        this._initHeader();
    },

    _initHeader: function() {

        // submit on ENTER
        this.searchBox.on('keypress', $.proxy(function(e) {
            if (e.which != 13) {
                return true;
            }

            var searchTerm = this.searchBox.val();
            this.doSearch(searchTerm);

            e.preventDefault();
            return false;
        }, this));

    },

    // Do the actual search
    doSearch: function(searchTerm) {

        $.ajax({
            url: this.getMetaData('newSearchUrl'),
            data: {
                search_term: searchTerm
            },
            context: this,
            success: function(html) {
            this.wrapper.find('.search-results').html(html);
            }
        });

    }

});
