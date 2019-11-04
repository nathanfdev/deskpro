import PropTypes from 'prop-types';
import React from 'react';
import { FormattedMessage } from 'react-intl';
import classNames from 'classnames';
import Isvg from 'react-inlinesvg';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import { HcOmniSearchResultSection, HcOmniSearchResultTickets } from 'DeskPRO/Bundle/PortalBundle/React/OmniSearch/HcOmniSearchResultSection';
import { ClickOut } from 'DeskPRO/Component/ClickOut';
import ChatsSvg from '@deskpro/portal-style/dist/img/page-icons/chats.svg';
import CommunitySvg from '@deskpro/portal-style/dist/img/page-icons/community.svg';
import DownloadSvg from '@deskpro/portal-style/dist/img/page-icons/download.svg';
import GuidesSvg from '@deskpro/portal-style/dist/img/page-icons/guides.svg';
import KnowledgebaseSvg from '@deskpro/portal-style/dist/img/page-icons/knowledgebase.svg';
import NewsSvg from '@deskpro/portal-style/dist/img/page-icons/news.svg';
import forOwn from 'lodash/forOwn';
import keys from 'lodash/keys';
import throttle from 'lodash/throttle';
import moment from 'moment';

class ResultTab extends React.PureComponent {
  static propTypes = {
    tab:       PropTypes.string,
    title:     PropTypes.string,
    icon:      PropTypes.string,
    data:      PropTypes.object,
    activeTab: PropTypes.string,
    onClick:   PropTypes.func,
  };

  constructor(props) {
    super(props);

    this.onClick = this.onClick.bind(this);
  }

  onClick(e) {
    e.stopPropagation();
    e.preventDefault();
    this.props.onClick(this.props.tab);
  }

  render() {
    const { tab, title, icon, data, activeTab } = this.props;
    if (!data[tab]) {
      return null;
    }
    return (
      <li className="dp-po-search-tabs-item">
        <a className={classNames('dp-po-search-tabs-link', { active: activeTab === tab, 'no-results': data[tab].results.length === 0 })} onClick={this.onClick} href="#">
          <Isvg src={icon} className="dp-po-search-tabs-image" wrapper={React.createFactory('div')} />
          <FormattedMessage id={title} tagName="div" />
          <span>{data[tab].pageinfo.total_results}</span>
        </a>
      </li>
    );
  }
}

export class HcOmniSearch extends React.Component {

  static propTypes = {
    $input:            PropTypes.object,
    $inputSearchLogId: PropTypes.object,
    $close:            PropTypes.object,
    $button:           PropTypes.object
  };

  constructor(props) {
    super(props);

    // grab searchLogId from search form itself
    // in case if user press search button
    const { $inputSearchLogId } = this.props;
    const searchLogId = $inputSearchLogId.val();

    this.state = {
      doSpin:          false, // a search is in progress
      lastSearch:      moment(), // the last time a user executed a search (typed something in)
      userTyping:      false,
      searchQuery:     '',
      lastSearchLogId: searchLogId || null,
      activeTab:       'article',
      data:            {
        pageinfo: {
          total_results: 0,
          curpage:       1
        }
      }
    };

    this.setTab = this.setTab.bind(this);
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
    $close.click(this.onClear);

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

  onClear = (event) => {
    event.preventDefault();

    this.props.$input.val('');
    this.props.$inputSearchLogId.val('');
    this.setState({
      data:            {},
      doSpin:          false,
      userTyping:      false,
      searchQuery:     '',
      lastSearchLogId: null
    });
  };

  onClickOut = (event) => {
    event.preventDefault();

    this.setState({
      data:        {},
      doSpin:      false,
      userTyping:  false,
      searchQuery: ''
    });
  };

  setTab(activeTab) {
    this.setState({
      activeTab
    });
  }

  doSearch(newQuery, lastSearchLogId) {
    const isNumericQuery = !isNaN(parseInt(newQuery, 10)) && !isNaN(newQuery - 0);
    if (!newQuery || (newQuery.length < 3 && !isNumericQuery)) {
      // we need a query with a length of at least 2 for the server to do any real searching
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

        this.props.$inputSearchLogId.val(logId);
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
    const { data, activeTab } = this.state;

    if (this.doResultsExist()) {
      return (
        <div>
          { data.ticket ?
            <HcOmniSearchResultTickets
              nameApi="ticket"
              nameIcon="far fa-life-ring"
              initialResult={'ticket' in data ? data.ticket : {}}
              q={this.state.searchQuery}
            />
          : null
          }
          { data.article || data.download || data.news || data.community || data.topic || data.chat_conversation ?
            <div className="dp-po-search-tabs">
              <ul className="dp-po-search-tabs-list">
                <ResultTab
                  tab="article"
                  title="helpcenter.general.nav-kb"
                  icon={KnowledgebaseSvg}
                  data={data}
                  activeTab={activeTab}
                  onClick={this.setTab}
                />
                <ResultTab
                  tab="topic"
                  title="helpcenter.general.nav-guides"
                  icon={GuidesSvg}
                  data={data}
                  activeTab={activeTab}
                  onClick={this.setTab}
                />
                <ResultTab
                  tab="community"
                  title="helpcenter.general.nav-community"
                  icon={CommunitySvg}
                  data={data}
                  activeTab={activeTab}
                  onClick={this.setTab}
                />
                <ResultTab
                  tab="news"
                  title="helpcenter.general.nav-news"
                  icon={NewsSvg}
                  data={data}
                  activeTab={activeTab}
                  onClick={this.setTab}
                />
                <ResultTab
                  tab="download"
                  title="helpcenter.general.nav-downloads"
                  icon={DownloadSvg}
                  data={data}
                  activeTab={activeTab}
                  onClick={this.setTab}
                />
                <ResultTab
                  tab="chat_conversation"
                  title="helpcenter.general.nav-chats"
                  icon={ChatsSvg}
                  data={data}
                  activeTab={activeTab}
                  onClick={this.setTab}
                />
              </ul>
              <HcOmniSearchResultSection
                nameApi="article"
                initialResult={'article' in data ? data.article : {}}
                q={this.state.searchQuery}
                activeTab={activeTab}
              />
              <HcOmniSearchResultSection
                nameApi="topic"
                initialResult={'topic' in data ? data.topic : {}}
                q={this.state.searchQuery}
                activeTab={activeTab}
              />
              <HcOmniSearchResultSection
                nameApi="community"
                initialResult={'community' in data ? data.community : {}}
                q={this.state.searchQuery}
                activeTab={activeTab}
              />
              <HcOmniSearchResultSection
                nameApi="news"
                initialResult={'news' in data ? data.news : {}}
                q={this.state.searchQuery}
                activeTab={activeTab}
              />
              <HcOmniSearchResultSection
                nameApi="download"
                initialResult={'download' in data ? data.download : {}}
                q={this.state.searchQuery}
                activeTab={activeTab}
              />
              <HcOmniSearchResultSection
                nameApi="chat_conversation"
                initialResult={'chat_conversation' in data ? data.chat_conversation : {}}
                q={this.state.searchQuery}
                activeTab={activeTab}
              />
            </div>
            : null
          }
        </div>
      );
    }

    return (
      <div className="search-result-collection-empty">
        <div><FormattedMessage id="portal.general.no-search-results-general" /></div>
      </div>
    );
  }

  render() {
    const { $input, $button } = this.props;
    const { searchQuery } = this.state;

    const isNumericQuery = !isNaN(parseInt(searchQuery, 10)) && !isNaN(searchQuery - 0);

    $input.removeClass('opened');

    if (searchQuery.length < 3  && !isNumericQuery) {
      return null;
    }

    $input.addClass('opened');

    return (
      <ClickOut onClickOut={this.onClickOut} additionalNodes={[$input, $button, '.search-results-show-more']}>
        <div
          className="dp-po-search-hint"
          style={{
            display: this.state.searchQuery.length > 0 ? 'block' : 'none',
            width:   $input.closest('.search-form').width()
          }}
        >

          {this.state.doSpin || (!this.doResultsExist() && this.state.userTyping)
            ? <div className="search-result-collection-loading" />
            : this.renderResults()
          }
        </div>
      </ClickOut>
    );
  }
}

