import React, { PropTypes } from 'react';
import PortalHttp from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import PortalUrlGenerator from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';
import OmniSearchResultSection from 'DeskPRO/Bundle/PortalBundle/React/OmniSearch/OmniSearchResultSection';
import _ from 'lodash';
import $ from 'jquery';
import moment from 'moment';

// Remove?
// class SearchType extends React.Component {
//
//  toggle() {
//    this.props.toggleType(this.props.type);
//  }
//  render() {
//    return (
//      <li><a className="omnisearch-type" onClick={this.toggle.bind(this)}>
//        {this.props.active ? <i className="fa fa-check"></i> : null} {this.props.name}</a>
//      </li>
//    );
//  }
// }

export default class OmniSearch extends React.Component {

  static propTypes = {
    input: PropTypes.object,
    close: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      doSpin: false, // a search is in progress
      lastSearch: moment(), // the last time a user executed a search (typed something in)
      userTyping: false,
      $input: $(props.input),
      $close: $(props.close),
      data: {
        pageinfo: {
          total_results: 0,
          curpage: 1
        }
      },
      searchQuery: {
        q: ''
      }
    };
  }

  componentDidMount() {
    // typing listener
    let lastVal = null;
    const throttleChanges = _.throttle((e) => {
      // ensure we don't trigger a search if the actual search val hasn't changed
      if (lastVal !== e.target.value) {
        lastVal = e.target.value;
        this.doSearch({q: e.target.value});
      }
    }, 250);

    this.state.$input.on('keyup change', throttleChanges);

    // close search button
    this.state.$close.click((e) => {
      e.preventDefault();
      this.state.$input.val('');
      this.doSearch({ q: '' }); // reset/close search
    });

    // 1000ms pause before showing "no results"
    this.interval = setInterval(() => {
      // update the "userTyping" state when necessary - check every 100ms
      const newUserTyping = moment().diff(this.state.lastSearch, 'milliseconds') < 1000;
      if (this.state.userTyping !== newUserTyping) {
        this.setState({
          userTyping: newUserTyping
        });
      }
    }, 100); // every 100ms check if the user has not typed in a while or not

    // clickaway handler
    $(document).on('keypress', event => {
      if (event.which === 13) { // Checks for the enter key
        // without this, hitting "enter" to view the search results page would count as a "click"
        // outside of the ominsearch and clear the search
        event.preventDefault();
      }
    });

    document.addEventListener('click', this.documentClickHandler.bind(this));
  }

  componentWillUnmount() {
    window.clearInterval(this.interval);
    document.removeEventListener('click', this.documentClickHandler.bind(this));
  }

  documentClickHandler(e) {
    const $target = $(e.target);
    // if we are clicking inside the search results its ok. but otherwise, we want to clear/close the search.
    if (!$target.closest('.expanded-search-results').length
        && !$target.closest('input.omnisearch').length
        && !$target.hasClass('dpx-omnisearch-link') // allow clicking of omnisearch links without closing it
    ) {
      this.state.$input.val('');
      this.doSearch({ q: '' }); // reset/close search
    }
  }

  doSearch(queryModifications) {
    const lastQuery = this.state.searchQuery || {};
    const searchQuery = {...lastQuery, ...queryModifications};
    this.setState({
      lastSearch: moment(),
      searchQuery
    });

    if (!searchQuery.q || searchQuery.q.length < 3) {
      // we need a query with a length of at least 3 for the server to do any real searching
      // so don't do a HTTP request if we don't at least have that
      return;
    }

    this.setState({
      doSpin: true
    });

    PortalHttp.sendGet('DP_URL/search/omni', { data: searchQuery }).then(response => {
      if (response.isError()) {
        return;
      }

      this.setState({
        data: response.data.data,
        searchQuery,
        doSpin: false
      });
    });
  }

  doResultsExist() {
    let grandTotal = 0;
    _.forOwn(this.state.data, (typeResults) => {
      if ('results' in typeResults) {
        grandTotal += _.keys(typeResults.results).length;
      }
    });

    return grandTotal > 0;
  }

  renderResults() {
    const data = this.state.data;

    if (this.doResultsExist()) {
      return (
        <div>
          <OmniSearchResultSection
            name="Tickets"
            nameApi="ticket"
            nameIcon="fa fa-support"
            initialResult={'ticket' in data ? data.ticket : []}
            q={this.state.searchQuery.q}
            />

          <OmniSearchResultSection
            name="Knowledge base"
            nameApi="article"
            nameIcon="fa fa-file-text-o"
            initialResult={'article' in data ? data.article : []}
            q={this.state.searchQuery.q}
            />

          <OmniSearchResultSection
            name="Downloads"
            nameApi="download"
            nameIcon="fa fa-download"
            initialResult={'download' in data ? data.download : []}
            q={this.state.searchQuery.q}
            />

          <OmniSearchResultSection
            name="News"
            nameApi="news"
            nameIcon="fa fa-file-text-o"
            initialResult={'news' in data ? data.news : []}
            q={this.state.searchQuery.q}
            />

          <OmniSearchResultSection
            name="Feedback"
            nameApi="feedback"
            nameIcon="fa fa-comments"
            initialResult={'feedback' in data ? data.feedback : []}
            q={this.state.searchQuery.q}
            />
        </div>
      );
    }

    return (
      <div className="search-result-collection-empty">
        <div>No Results found :(</div>
      </div>
    );
  }

  render() {
    if (this.state.searchQuery.q.length < 3) {
      return null;
    }

    return (
      <div
        ref="searchDropdown"
        className="expanded-search-results"
        style={{
          display: this.state.searchQuery.q.length > 0 ? 'block' : 'none',
          width: this.state.$input.closest('.search-form').width()
        }}>

        {this.state.doSpin || (!this.doResultsExist() && this.state.userTyping)
          ? <div className="search-result-collection-loading"></div>
          : this.renderResults()
        }

        <div className="search-results-footer">
          <a href={PortalUrlGenerator.path('/new-ticket')}>
            <i className="fa fa-comment"></i>
            <span>Contact Us</span>
          </a>
          <a href={PortalUrlGenerator.path('/feedback')}>
            <i className="fa fa-list"></i>
            <span>Submit Feedback</span>
          </a>
          <a href="#">
            <i className="fa fa-comments"></i>
            <span>Start Chat Session</span>
          </a>
        </div>
      </div>
    );
  }
}

