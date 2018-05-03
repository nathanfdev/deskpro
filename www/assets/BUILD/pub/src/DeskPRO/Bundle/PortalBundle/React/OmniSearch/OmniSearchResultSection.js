import PropTypes from 'prop-types';
import React from 'react';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import filter from 'lodash/filter';
import forEach from 'lodash/forEach';
import map from 'lodash/map';
import TimeAgo from 'react-timeago';
import moment from 'moment';
import { timeAgoFormatter } from '../../../WidgetBundle/Services/timeago';

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

export class OmniSearchResultSection extends React.Component {

  static propTypes = {
    name:          PropTypes.string,
    nameApi:       PropTypes.string,
    nameIcon:      PropTypes.string,
    initialResult: PropTypes.object,
    q:             PropTypes.string
  };

  constructor(props) {
    super(props);

    this.state = {
      name:                 props.name,
      nameApi:              props.nameApi,
      nameIcon:             props.nameIcon,
      display_amount:       10,
      currently_displaying: 10,
      page:                 1,
      total_results:        parseInt(props.initialResult.length ? props.initialResult.pageinfo.total_results : 0, 10),
      q:                    props.q,
      doSpin:               false
    };

    this.state.items = this.createsItemsFromProps(props);
    this.showMore = this.showMore.bind(this);
  }

  componentWillReceiveProps(newProps) {
    this.setState({
      name:                 this.props.name,
      nameApi:              this.props.nameApi,
      nameIcon:             this.props.nameIcon,
      display_amount:       10,
      currently_displaying: 10,
      page:                 1,
      total_results:        parseInt(newProps.initialResult.length ? newProps.initialResult.pageinfo.total_results : 0, 10),
      q:                    newProps.q,
      doSpin:               false,
      items:                this.createsItemsFromProps(newProps)
    });
  }

  getShowMoreNum() {
    const diff = parseInt(this.state.total_results, 10) - parseInt(this.state.currently_displaying, 10);
    if (diff > 10) {
      return 10;
    } else if (diff > 0) {
      return diff;
    }

    return null;
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

    if (this.state.nameApi === 'news') {
      t = (
        <span>
          <span className="date-mark">
            <i className="fa fa-calendar-o" />
            <TimeAgo
              className="dpdesignportal-event-time"
              formatter={timeAgoFormatter}
              minPeriod={60000}
              date={moment(item.date)}
            />
          </span>
          <span className="item-name">{item.name}</span>
        </span>
      );
    } else if (this.state.nameApi === 'feedback') {
      const sign = item.rating < 0 ? '-' : '+';

      t = (
        <span>
          <span className="feedback-mark">
            <i className="fa fa-thumbs-up" /> {sign + item.rating}
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
      <li key={item.id}>
        <a href={item.url}>{t}</a>
      </li>
    );
  }

  render() {
    const { nameIcon, name } = this.props;

    if (this.state.items.isEmpty()) {
      return null;
    }

    return (
      <div className="search-result-collection">
        <h1><i className={nameIcon} /> {name}</h1>
        <ul>
          {map(this.state.items.getNum(this.state.currently_displaying), item => this.renderItem(item))}
        </ul>

        {this.getShowMoreNum() !== null && !this.state.doSpin &&
          <a onClick={this.showMore} className="search-results-show-more">
            {this.getShowMoreNum()} More <i className="fa fa-angle-double-down" />
          </a>
        }

        {this.state.doSpin && <div className="search-result-collection-loading inline-loading" />}
      </div>
    );
  }
}
