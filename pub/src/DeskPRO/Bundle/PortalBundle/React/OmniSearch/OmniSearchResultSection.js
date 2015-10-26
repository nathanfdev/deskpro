import React from "react"
import _ from "lodash"
import PortalHttp from "DeskPRO/Bundle/PortalBundle/Http/PortalHttp"

class SearchResultCollection {
  constructor() {
    this.items = {};
    this.ordered_items = [];
  }
  addItem(item) {
    // only add unique
    const same = _.filter(this.items, (a) => a.id === item.id);
    if (same.length > 0) {
      // exit if we have it in the collection already
      return null;
    }
    this.items[item.id] = item;
    this.ordered_items.push(item);
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

class ListLink extends React.Component {
  render() {
    return (
      <li><a href={this.props.url}>{this.props.text}</a></li>
    );
  }
}

export default class OmniSearchResultSection extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      name: this.props.name,
      nameApi: this.props.nameApi,
      nameIcon: this.props.nameIcon,
      display_amount: 10,
      currently_displaying: 10,
      page: 1,
      total_results: _.parseInt(props.initialResult.pageinfo.total_results),
      q: props.q,
      doSpin: false
    };
    this.state.items = this.createsItemsFromProps(props);
  }
  createsItemsFromProps(props) {
    const theItems = new SearchResultCollection();
    _.forEach(props.initialResult.results, (item) => {
      theItems.addItem(item.object);
    });
    return theItems;
  }
  componentWillReceiveProps(newProps) {
    this.setState({
      name: this.props.name,
      nameApi: this.props.nameApi,
      nameIcon: this.props.nameIcon,
      display_amount: 10,
      currently_displaying: 10,
      page: 1,
      total_results: _.parseInt(newProps.initialResult.pageinfo.total_results),
      q: newProps.q,
      doSpin: false,
      items: this.createsItemsFromProps(newProps)
    });
  }
  addItems(items) {
    const theItems = this.state.items;
    _.forEach(items, (val) => {
      theItems.addItem(val.object);
    });
    this.setState({
      items: theItems
    });
  }
  grabFromApi() {

    console.info('calling API...');

    this.setState({
      doSpin: true
    });

    return new Promise((resolve, reject) => {
      const newpage = this.state.page + 1;
      PortalHttp.sendGet('DP_URL/search/omni', {
        data: {
          q: this.state.q,
          page: newpage,
          "types[]": this.state.nameApi
        }
      }).then((r) => {
        if (!r.isError()) {
          const result_data = r.data.data[this.state.nameApi];
          this.setState({
            doSpin: false,
            page: newpage,
            total_results: _.parseInt(result_data.pageinfo.total_results)
          });
          resolve(result_data);
        }
      });
    });
  }
  showMore() {
    const pending_total_display = this.state.currently_displaying + this.state.display_amount;
    if (pending_total_display > this.state.items.getTotal()) {
      this.grabFromApi().then((items) => {
        if ("results" in items) {
          this.addItems(items.results);
        }
      });
    }

    this.setState({
      currently_displaying: this.state.currently_displaying + this.state.display_amount
    });
  }
  getShowMoreNum() {
    const diff = _.parseInt(this.state.total_results) - _.parseInt(this.state.currently_displaying);
    if (diff > 10) {
      return 10;
    } else if (diff > 0) {
      return diff;
    } else {
      return null;
    }
  }
  render() {
    if (this.state.items.isEmpty()) {
      return null;
    }
    return (
      <div className="search-result-collection">
        <h1><i className={this.props.nameIcon}></i> {this.props.name}</h1>
        <ul>
          {_.map(this.state.items.getNum(this.state.currently_displaying), (item) => {
            let t = item.name;
            if (this.state.nameApi === 'news') {
              t = (<span><span className="date-mark"><i className="fa fa-calendar-o"></i> 10 days ago</span>{item.name}</span>);
            } else if (this.state.nameApi === 'feedback') {
              t = (<span><span className="feedback-mark"><i className="fa fa-thumbs-up"></i>+12</span>{item.name}</span>);
            }
            return (<ListLink key={item.id} url={item.url} text={t}/>);
          })}
        </ul>

        { this.getShowMoreNum() !== null ? (
          <a onClick={this.showMore.bind(this)} className="search-results-show-more">{this.getShowMoreNum()} More <i
            className="fa fa-angle-double-down"></i></a>
        ) : null}

      </div>
    );
  }
}

