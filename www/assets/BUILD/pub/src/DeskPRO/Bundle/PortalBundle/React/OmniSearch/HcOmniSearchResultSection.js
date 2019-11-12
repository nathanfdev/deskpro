import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import { FormattedMessage } from 'react-intl';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import filter from 'lodash/filter';
import forEach from 'lodash/forEach';
import map from 'lodash/map';
import TimeAgo from 'react-timeago';
import moment from 'moment';
import { timeAgoFormatter } from '../../../WidgetBundle/Services/timeago';
import { portalUrlGenerator } from '../../Http/PortalUrlGenerator';

class SearchResultCollection {

  constructor() {
    this.items = {};
    this.ordered_items = [];
  }

  addItem(item) {
    // only add unique
    const same = filter(this.items, a => a.id === item.id);

    if (same.length > 0) {
      // exit if we have it in the collection already
      return null;
    }

    this.items[item.id] = item;
    this.ordered_items.push(item);

    return this;
  }

  getNum(num) {
    return this.ordered_items.slice(0, num);
  }

  isEmpty() {
    return this.getTotal() === 0;
  }

  getTotal() {
    return this.ordered_items.length;
  }
}

export class HcOmniSearchResultSection extends React.Component {

  static propTypes = {
    nameApi:       PropTypes.string,
    initialResult: PropTypes.object,
    q:             PropTypes.string,
    activeTab:     PropTypes.string,
  };

  constructor(props) {
    super(props);

    this.state = {
      nameApi:              props.nameApi,
      display_amount:       10,
      currently_displaying: 10,
      page:                 1,
      total_results:        parseInt(props.initialResult.results && props.initialResult.results.length ? props.initialResult.pageinfo.total_results : 0, 10),
      q:                    props.q,
      doSpin:               false
    };

    this.state.items = this.createsItemsFromProps(props);
    this.showMore = this.showMore.bind(this);
  }

  componentWillReceiveProps(newProps) {
    this.setState({
      nameApi:              this.props.nameApi,
      display_amount:       10,
      currently_displaying: 10,
      page:                 1,
      total_results:        parseInt(newProps.initialResult.results && newProps.initialResult.results.length ? newProps.initialResult.pageinfo.total_results : 0, 10),
      q:                    newProps.q,
      doSpin:               false,
      items:                this.createsItemsFromProps(newProps)
    });
  }

  grabFromApi() {
    this.setState({
      doSpin: true
    });

    return new Promise((resolve) => {
      const newpage = this.state.page + 1;

      portalHttp.sendGet('DP_URL/search/omni', {
        data: {
          q:         this.state.q,
          page:      newpage,
          'types[]': this.state.nameApi
        }
      }).then((response) => {
        if (response.isError()) {
          return;
        }

        const resultData = response.data.data[this.state.nameApi];
        this.setState({
          doSpin:        false,
          page:          newpage,
          total_results: parseInt(resultData.pageinfo.total_results, 10)
        });

        resolve(resultData);
      });
    });
  }

  showMore() {
    const pendingTotalDisplay = this.state.currently_displaying + this.state.display_amount;
    if (pendingTotalDisplay > this.state.items.getTotal()) {
      this.grabFromApi().then((items) => {
        if ('results' in items) {
          this.addItems(items.results);
        }
      });
    }

    this.setState({
      currently_displaying: this.state.currently_displaying + this.state.display_amount
    });
  }

  addItems(items) {
    const theItems = this.state.items;
    forEach(items, (val) => {
      theItems.addItem(val.object);
    });
    this.setState({
      items: theItems
    });
  }

  createsItemsFromProps(props) { // eslint-disable-line
    const theItems = new SearchResultCollection();
    forEach(props.initialResult.results, (item) => {
      theItems.addItem(item.object);
    });

    return theItems;
  }

  renderItem(item) {
    let t;

    console.log(item);

    if (true || this.state.nameApi === 'news') {
      t = (
        <a href={item.url} className="dp-po-search-link">
          {item.name}
          <div className="dp-po-time">
            <TimeAgo
              className="dpdesignportal-event-time"
              formatter={timeAgoFormatter}
              minPeriod={60000}
              date={moment(item.date)}
            />
            <i className="dp-po-icon far fa-clock" />
          </div>
        </a>
      );
    } else if (this.state.nameApi === 'community') {
      const sign = item.rating < 0 ? '-' : '+';

      t = (
        <span>
          <span className="feedback-mark">
            <i className="fas fa-thumbs-up" /> {sign + item.rating}
          </span>
          <span className="item-name">{item.name}</span>
        </span>
      );
    } else if (this.state.nameApi === 'download') {
      t = (
        <span>
          <span dangerouslySetInnerHTML={{ __html: item.icon_html }} />
          <span className="item-name">{item.name}</span>
        </span>
      );
    } else {
      t = <span className="item-name">{item.name}</span>;
    }

    return (
      <li className="dp-po-search-item" key={item.id}>
        {t}
      </li>
    );
  }

  render() {
    const { nameApi, activeTab, q } = this.props;
    const { total_results } = this.state;

    if (this.state.items.isEmpty() || nameApi !== activeTab) {
      return null;
    }

    return (
      <div className="search-result-collection">
        <ul className="dp-po-search-list">
          {map(this.state.items.getNum(this.state.currently_displaying), item => this.renderItem(item))}
        </ul>

        <a href={portalUrlGenerator.path(`/search/${nameApi}?q=${q}`)} className="dp-po-search-hint-viewall">
          <FormattedMessage id="helpcenter.search.view-all-results" values={{ count: total_results }} />
        </a>

        {this.state.doSpin && <div className="search-result-collection-loading inline-loading" />}
      </div>
    );
  }
}

export class HcOmniSearchResultTickets extends React.Component {

  static propTypes = {
    nameApi:       PropTypes.string,
    initialResult: PropTypes.object,
    q:             PropTypes.string
  };

  static renderItem(item) {
    return (
      <li className="dp-po-search-item" key={item.id}>
        <a href={item.url} className="dp-po-search-link">
          {item.name}
          <div className="dp-po-time">
            <i className="dp-po-icon far fa-clock" />
            <TimeAgo
              className="dpdesignportal-event-time"
              formatter={timeAgoFormatter}
              minPeriod={60000}
              date={moment(item.date)}
            />
          </div>
        </a>
      </li>
    );
  }

  constructor(props) {
    super(props);

    this.state = {
      nameApi:              props.nameApi,
      display_amount:       10,
      currently_displaying: 10,
      page:                 1,
      totalResults:         parseInt(props.initialResult.results.length ? props.initialResult.pageinfo.total_results : 0, 10),
      q:                    props.q,
      doSpin:               false
    };

    this.state.items = this.createsItemsFromProps(props);
    // this.showMore = this.showMore.bind(this);
  }

  createsItemsFromProps(props) { // eslint-disable-line
    const theItems = new SearchResultCollection();
    forEach(props.initialResult.results, (item) => {
      theItems.addItem(item.object);
    });

    return theItems;
  }

  render() {
    const { totalResults } = this.state;
    const { q } = this.props;

    return (
      <div className={classNames('dp-po-search-hint-tickets', { 'no-results': this.state.items.isEmpty() })}>
        <div className="dp-po-search-hint-header">
          <h3 className="dp-po-search-hint-header-title"><i className="dp-po-icon fad fa-envelope" /> <FormattedMessage id="helpcenter.search.your-tickets" tagName="div" />
            <span>{totalResults}</span>
          </h3>
        </div>
        {this.state.items.isEmpty() ? null :
        <div>
          <ul className="dp-po-search-list">
            {map(this.state.items.getNum(this.state.currently_displaying), item => HcOmniSearchResultTickets.renderItem(item))}
          </ul>
          <a href={portalUrlGenerator.path(`/search/ticket?q=${q}`)} className="dp-po-search-hint-viewall"><FormattedMessage id="helpcenter.search.view-all-results" values={{ count: totalResults }} /></a>
        </div>
        }
      </div>
    );
  }
}
