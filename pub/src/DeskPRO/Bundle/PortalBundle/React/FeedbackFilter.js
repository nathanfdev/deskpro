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

class FilterModel {
  constructor(data) {
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
  createUrl() {
    let url = '/feedback/browse/';

    url += this.status;

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
    if (this.status != status_id) {
      this.status = status_id;
      this.page = 1;
    }
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
    this.state = {
      filter: new FilterModel(this.props.filter_data.filter)
    };
  }
  componentDidMount() {
    history.replaceState(this.state, null, window.history.location || window.location);
    this.updateFilter(this.state.filter);
    window.addEventListener('popstate', (e) => {
      if (e.state == null) {
        return;
      }
      let nstate = {filter: new FilterModel(e.state.filter), partial: e.state.partial};
      this.setState(nstate);
    });
  }
  updateFilter(filter_model) {
    console.log ('updating to this filter', filter_model);
    this.setState({
      filter: this.state.filter,
      partial: '<div style="min-height: 800px;"><i>loading...</i></div>'
    }, () => {
      let http = new Http($.ajax);
      let url = filter_model.createUrl().getUrl();

      http.sendGet(url).then(r => {
        console.log(url, r);

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
                        available={this.props.filter_data.available}
                        updateFilter={this.updateFilter.bind(this)} />
        <FeedbackResults filterModel={this.state.filter}
                         partial={this.state.partial}
                         updateFilter={this.updateFilter.bind(this)} />
      </article>
    );
  }
}
