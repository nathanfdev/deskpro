import React from "react"
import FilterControls from "DeskPRO/Bundle/PortalBundle/React/FilterControls"
import Http from "DeskPRO/Component/Http/Http"
import history from "html5-history-api"
import _ from "lodash"
import $ from "jquery"

class FeedbackResults extends React.Component {
  render() {
    return (
      <div className="paged-results" ref="results" dangerouslySetInnerHTML={{ __html: this.props.partial }}>
      </div>
    );
  }
  componentDidMount() {
    this.setEvents();
  }
  updatePage(page) {
    let new_filter = this.props.filterModel;
    new_filter.page = page;
    this.props.updateFilter(new_filter);
  }
  componentDidUpdate() {
    this.setEvents();
  }
  setEvents() {
    let results = $(React.findDOMNode(this.refs.results));
    let that = this;
    results.find('.deskpro-pager a').each(function () {
      $(this).click(function(e){
        e.preventDefault();
        let uri = $(this).attr('href');
        let getparam = function get(n) {
          var half = uri.split(n + '=')[1];
          return half !== undefined ? decodeURIComponent(half.split('&')[0]) : null;
        };
        that.updatePage(getparam('page'));
      });
    });
  }
}

class FilterOptions {
  constructor(available) {
    this._available = available;
    this.status = available.status;
    this.status_categories = available.status_categories;
    this.types = available.types;
    this.sorts = available.sorts;
    this.sort_directions = available.sort_directions;
  }
  getStatusForStatusCategory(status_category_id) {
    let result = null;

    _.forEach(this.status_categories, (st_cats, st_id) => {
      _.forEach(st_cats, (st_cat) => {
        if (st_cat.id === status_category_id) {
          result = st_id;
        }
      });

    });
    return result;
  }
  getStatusCategoriesForStatus(status_id) {
    let result = [];
    _.forEach(this.status_categories, (st_cats, st_id) => {
      if (st_id == status_id) {
        result = st_cats;
      }
    });
    return result;
  }
}

class FilterModel {
  constructor(data, available) {
    this.available = available;
    this.sort = data.sort;
    this.sort_direction = data.sort_direction;
    this.status = data.status;
    this.status_categories = _.map(data.status_categories, function(val) {
      return _.parseInt(val);
    });
    this.types = _.map(data.types, function(val) {
      return _.parseInt(val);
    });
    this.page = _.parseInt(data.page || 1);
  }
  getAvailable() {
    return this.available;
  }
  createUrl() {
    let url = '/feedback/browse/';

    url += this.status;

    if (this.status_categories.length > 0) {
      url += '-' + this.status_categories.join(',');
    }

    if (this.types.length > 0) {
      url += '/type-';
      url += this.types.join(',');
    }

    if (this.page > 1) {
      url += '?page=' + this.page;
    }

    return new FilterUrl(url);
  }
  setStatus(status_id) {
    this.page = 1;
    if (this.status != status_id) {
      this.status = status_id;
    }
    let avil = _.map(this.available.getStatusCategoriesForStatus(this.status), (cat) => {
        return cat.id;
    });
    this.status_categories = _.filter(this.status_categories, (cat) => {
      return _.includes(avil, cat);
    });
  }
  getStatus() {
    return this.status;
  }
  toggleType(type_id) {
    this.page = 1;
    type_id = _.parseInt(type_id);
    if (_.includes(this.types, type_id)) {
      this.types = _.filter(this.types, (n) => {
        return n != type_id;
      });
    } else {
      this.types.push(type_id);
    }
  }
  toggleStatusCategory(status_category_id) {
    this.page = 1;
    status_category_id = _.parseInt(status_category_id);
    if (_.includes(this.status_categories, status_category_id)) {
      this.status_categories = _.filter(this.status_categories, (n) => {
        return n != status_category_id;
      });
    } else {
      this.status_categories.push(status_category_id);
    }
    // force a filter on this.status_categories
    this.setStatus(this.available.getStatusForStatusCategory(status_category_id));
  }
}

class FilterUrl {
  constructor(url_string) {
    this.url = url_string;
  }
  getUrl() {
    return this.url;
  }
}

export default class FeedbackFilter extends React.Component {
  constructor(props) {
    super(props);
    let available = new FilterOptions(this.props.filter_data.available);
    let filter = new FilterModel(this.props.filter_data.filter, available);
    this.state = {
      available: available,
      filter: filter
    };
  }
  componentDidMount() {
    history.replaceState(this.state, null, window.history.location || window.location);
    this.updateFilter(this.state.filter);
    window.addEventListener('popstate', (e) => {
      if (e.state == null) {
        return;
      }
      this.setState({
        filter: new FilterModel(e.state.filter, this.state.available),
        partial: e.state.partial}
      );
    });
  }
  updateFilter(filter_model) {
    this.setState({
      filter: this.state.filter,
      partial: '<div style="min-height: 800px;"><i>loading...</i></div>'
    }, () => {
      let http = new Http($.ajax);
      let url = filter_model.createUrl().getUrl();

      http.sendGet(url).then(r => {
        let state = {
          filter: filter_model,
          partial: r.getData()
        };
        history.pushState(state, null, url);
        this.setState(state);
    });
    });
  }
  render() {
    return (
      <article className="feedback-filter-interactive">
        <FilterControls filterModel={this.state.filter}
                        available={this.state.available}
                        updateFilter={this.updateFilter.bind(this)} />
        <FeedbackResults filterModel={this.state.filter}
                         partial={this.state.partial}
                         updateFilter={this.updateFilter.bind(this)} />
      </article>
    );
  }
}
