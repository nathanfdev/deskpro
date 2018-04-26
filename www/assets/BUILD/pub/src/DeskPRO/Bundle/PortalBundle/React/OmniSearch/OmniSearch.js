import PropTypes from 'prop-types';
import React from 'react';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import { portalUrlGenerator } from 'DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator';
import { OmniSearchResultSection } from 'DeskPRO/Bundle/PortalBundle/React/OmniSearch/OmniSearchResultSection';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import forOwn from 'lodash/forOwn';
import keys from 'lodash/keys';
import throttle from 'lodash/throttle';
import moment from 'moment';

export class OmniSearch extends React.Component {

  static propTypes = {
    $input:  PropTypes.object,
    $close:  PropTypes.object,
    $button: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      doSpin:          false, // a search is in progress
      lastSearch:      moment(), // the last time a user executed a search (typed something in)
      userTyping:      false,
      searchQuery:     '',
      lastSearchLogId: null,
      data:            {
        pageinfo: {
          total_results: 0,
          curpage:       1
        }
      }
    };
  }

  componentDidMount() {
    const { $input, $close } = this.props;

    // typing listener
    let lastVal = null;
    const throttleChanges = throttle((e) => {
      // ensure we don't trigger a search if the actual search val hasn't changed
      if (lastVal !== e.target.value) {
        lastVal = e.target.value;
        this.doSearch(lastVal, this.state.lastSearchLogId);
      }
    }, 700);

    $input.on('keyup change', event => throttleChanges(event));
    $close.click(this.onClickOut);

    // 1000ms pause before showing "no results"
    this.interval = setInterval(() => {
      // update the "userTyping" state when necessary - check every 100ms
      const newUserTyping = moment().diff(this.state.lastSearch, 'milliseconds') < 1200;
      if (this.state.userTyping !== newUserTyping) {
        this.setState({
          userTyping: newUserTyping
        });
      }
    }, 100); // every 100ms check if the user has not typed in a while or not
  }

  componentWillUnmount() {
    window.clearInterval(this.interval);
  }

  onClickOut = (event) => {
    event.preventDefault();

    this.props.$input.val('');
    this.setState({
      data:            {},
      doSpin:          false,
      userTyping:      false,
      searchQuery:     '',
      lastSearchLogId: null
    });
  };

  doSearch(newQuery, lastSearchLogId) {
    if (!newQuery || newQuery.length < 3) {
      // we need a query with a length of at least 3 for the server to do any real searching
      // so don't do a HTTP request if we don't at least have that
      return;
    }

    this.setState({
      data:        {},
      doSpin:      true,
      userTyping:  true,
      searchQuery: newQuery,
      lastSearch:  moment()
    }, () => {
      const searchData = {
        q: newQuery
      };
      if (lastSearchLogId) {
        searchData.search_log_id = lastSearchLogId;
      }
      portalHttp.sendGet('DP_URL/search/omni', { data: searchData }).then((response) => {
        if (response.isError() || (newQuery !== this.state.searchQuery)) {
          return;
        }

        let logId = null;
        if ('meta' in response.data.data) {
          logId = response.data.data.meta.search_log_id;
          delete response.data.data.meta;
        }

        this.setState({
          data:            response.data.data,
          doSpin:          false,
          lastSearchLogId: logId
        });
      });
    });
  }

  doResultsExist() {
    let grandTotal = 0;
    forOwn(this.state.data, (typeResults) => {
      if ('results' in typeResults) {
        grandTotal += keys(typeResults.results).length;
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
            name={portalPhrases.get('portal.general.nav-tickets')}
            nameApi="ticket"
            nameIcon="fa fa-support"
            initialResult={'ticket' in data ? data.ticket : {}}
            q={this.state.searchQuery}
          />
          <OmniSearchResultSection
            name={portalPhrases.get('portal.general.nav-kb')}
            nameApi="article"
            nameIcon="fa fa-file-text-o"
            initialResult={'article' in data ? data.article : {}}
            q={this.state.searchQuery}
          />
          <OmniSearchResultSection
            name={portalPhrases.get('portal.general.nav-downloads')}
            nameApi="download"
            nameIcon="fa fa-download"
            initialResult={'download' in data ? data.download : {}}
            q={this.state.searchQuery}
          />
          <OmniSearchResultSection
            name={portalPhrases.get('portal.general.nav-news')}
            nameApi="news"
            nameIcon="fa fa-file-text-o"
            initialResult={'news' in data ? data.news : {}}
            q={this.state.searchQuery}
          />
          <OmniSearchResultSection
            name={portalPhrases.get('portal.general.nav-feedback')}
            nameApi="feedback"
            nameIcon="fa fa-comments"
            initialResult={'feedback' in data ? data.feedback : {}}
            q={this.state.searchQuery}
          />
          <OmniSearchResultSection
            name={portalPhrases.get('portal.general.nav-guides')}
            nameApi="topic"
            nameIcon="fa fa-book"
            initialResult={'topic' in data ? data.topic : {}}
            q={this.state.searchQuery}
          />
          <OmniSearchResultSection
            name={portalPhrases.get('portal.general.nav-chat')}
            nameApi="chat_conversation"
            nameIcon="fa fa-comments"
            initialResult={'chat_conversation' in data ? data.chat_conversation : {}}
            q={this.state.searchQuery}
          />
        </div>
      );
    }

    return (
      <div className="search-result-collection-empty">
        <div>{portalPhrases.get('portal.general.no-search-results-general')}</div>
      </div>
    );
  }

  render() {
    const { $input, $button } = this.props;

    if (this.state.searchQuery.length < 3) {
      return null;
    }

    return (
      <ClickOut onClickOut={this.onClickOut} additionalNodes={[$input, $button, '.search-results-show-more']}>
        <div
          className="expanded-search-results"
          style={{
            display: this.state.searchQuery.length > 0 ? 'block' : 'none',
            width:   $input.closest('.search-form').width()
          }}
        >

          {this.state.doSpin || (!this.doResultsExist() && this.state.userTyping)
            ? <div className="search-result-collection-loading" />
            : this.renderResults()
          }

          <div className="search-results-footer">
            {window.DESKPRO_CAN_USE_TICKETS &&
              <a href={portalUrlGenerator.path('/new-ticket')}>
                <i className="fa fa-comment" />
                <span>{portalPhrases.get('portal.general.nav-newticket')}</span>
              </a>}

            {window.DESKPRO_CAN_USE_FEEDBACK &&
              <a href={portalUrlGenerator.path('/feedback')}>
                <i className="fa fa-list" />
                <span>{portalPhrases.get('portal.general.submit-feedback')}</span>
              </a>}
            {window.DESKPRO_CAN_USE_CHAT &&
              <a href={portalUrlGenerator.path('/chat-logs')}>
                <i className="fa fa-comments" />
                <span>{portalPhrases.get('portal.general.start-chat')}</span>
              </a>}
          </div>
        </div>
      </ClickOut>
    );
  }
}

