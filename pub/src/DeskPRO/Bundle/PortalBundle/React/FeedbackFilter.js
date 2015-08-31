import React from "react"
import FilterControls from "DeskPRO/Bundle/PortalBundle/React/FilterControls"
import Http from "DeskPRO/Component/Http/Http"
import history from "html5-history-api"
import _ from "lodash"
import $ from "jquery"

class FeedbackResults extends React.Component {
  render() {
    let html = this.props.partial;
    if (html.length === 0) {
      return (
        <div className="paged-results centered" ref="results">
          <img
            style={{display: this.props.doSpin ? "table" : "none", margin: "0 auto", height: "70px", width: "70px"}}
             src={ window.DESKPRO_BASE_URL + '/web/spinner.gif' } />
        </div>
      );
    }
    return (
      <div className="paged-results" ref="results" dangerouslySetInnerHTML={{ __html: html }}>
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
  setStatusCategory(id) {
    let new_filter = this.props.filterModel;
    new_filter.reset();
    new_filter.setStatusCategory(id);
    this.props.updateFilter(new_filter);
  }
  setType(id) {
    let new_filter = this.props.filterModel;
    new_filter.reset();
    new_filter.setType(id);
    this.props.updateFilter(new_filter);
  }
  componentDidUpdate() {
    this.setEvents();
  }
  setEvents() {
    let self = this;
    let results = $(React.findDOMNode(this.refs.results));

    // process pager
    results.find('.deskpro-pager a').each(function () {
      $(this).click(function(e){
        e.preventDefault();
        let uri = $(this).attr('href');
        let getparam = function get(n) {
          let half = uri.split(n + '=')[1];
          return half !== undefined ? decodeURIComponent(half.split('&')[0]) : null;
        };
        self.updatePage(getparam('page'));

        return false;
      });
    });

    // add events to "I Agree"
    results.find('.feedback-item-controls a.i-agree').each(function() {
      let $iAgreeBox = $(this);
      $iAgreeBox.click(function (e) {
        e.preventDefault();
        if ($iAgreeBox.hasClass('agreed')) {
          return; // already agreed
        }
        let action = $iAgreeBox.attr('href');
        $.post(action);
        let $counter = $iAgreeBox.find('span.counter');
        $counter.text(_.parseInt($counter.text()) + 1);
        $iAgreeBox.addClass('agreed');

        return false;
      });
    });

    // add events to status category links
    results.find('.feedback-item-content .feedback-status a').each(function () {
      let $statusCategoryLink = $(this);
      $statusCategoryLink.click(function (e) {
        e.preventDefault();
        self.setStatusCategory($statusCategoryLink.data('id'));
        return false;
      });
    });

    // add events to type (categories) links
    results.find('a.feedback-category').each(function () {
      let $typeLink = $(this);
      $typeLink.click(function (e) {
        e.preventDefault();
        self.setType($typeLink.data('id'));
        return false;
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
  getStatusCategoryById(status_id, status_category_id) {
    let f = _.filter(this.status_categories[status_id], (cat) => {
      return cat.id == status_category_id;
    });
    return _.first(f);
  }
  getAvailableTypeIds() {
    let result = [];
    _.forEach(this.types, (type_name, type_id) => {
      result.push(_.parseInt(type_id));
    });
    return result;
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
  getStatusCategoryIdsForStatus(status_id) {
    return _.map(this.getStatusCategoriesForStatus(status_id), 'id');
  }
}

class FilterModel {
  constructor(data, available) {
    this.available = available;
    this.sort = data.sort;
    this.sort_direction = data.sort_direction || 'desc';
    this.status = data.status;
    this.status_categories = _.map(data.status_categories, function(val) {
      return _.parseInt(val);
    });
    this.types = _.map(data.types, function(val) {
      return _.parseInt(val);
    });
    this.page = _.parseInt(data.page || 1);
    this.checkEmptyTypes();
    this.checkEmptyStatusCategories();
  }
  reset() {
    this.page = 1;
    this.sort = 'date';
    this.sort_direction = 'desc';
    this.status = 'all';
    this.status_categories = [];
    this.types = [];
    this.checkEmptyTypes();
    this.checkEmptyStatusCategories();
  }
  changeSort(new_sort) {
    let parts = new_sort.split('-');
    if (parts.length == 2) {
      this.sort = parts[0];
      this.sort_direction = parts[1];
    }
    if (parts.length == 3) {
      this.sort = parts[0] + '-' + parts[1];
      this.sort_direction = parts[2];
    }
  }
  checkEmptyTypes() {
    if (this.types.length === 0) {
      // if no types are checked, default back to all types
      // this is the behaviour of the URL
      this.types = this.available.getAvailableTypeIds();
    }
  }
  checkEmptyStatusCategories() {
    if (this.status_categories.length === 0) {
      this.status_categories = this.available.getStatusCategoryIdsForStatus(this.status);
    }
    // if no status cats are picked and this status has some, check em all!
  }
  getAvailable() {
    return this.available;
  }
  createUrl() {
    let url = window.DESKPRO_BASE_URL + 'feedback/browse/';

    url += this.status;

    if (this.status_categories.length > 0) {
      url += '-' + this.status_categories.join(',');
    }

    if (this.types.length > 0) {
      let diff = _.difference(this.available.getAvailableTypeIds(), this.types);
      if (diff.length > 0) {
        // we only add the /type-x to the URL if it's a subset of types. default is to
        // include them all. if user has all selected, then we don't need it.
        // _.intersection above with a length of > 0 means the arrays have diff elements.
        url += '/type-';
        url += this.types.join(',');
      }
    }

    if (this.sort) {
      url += '/' + this.sort;
      if (this.sort_direction) {
        url += '-' + this.sort_direction;
      }
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
    this.checkEmptyStatusCategories();
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
    this.checkEmptyTypes();
  }
  setType(type_id) {
    this.page = 1;
    type_id = _.parseInt(type_id);
    this.types = [];
    this.types.push(type_id);
    this.checkEmptyTypes();
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
  setStatusCategory(status_category_id) {
    this.page = 1;
    status_category_id = _.parseInt(status_category_id);
    this.status_categories = [];
    this.status_categories.push(status_category_id);
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
      filter: filter,
      doSpin: true,
      partial: ''
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
          partial: e.state.partial,
          doSpin: e.state.doSpin
        }
      );
    });
  }
  updateFilter(filter_model) {
    this.setState({
      filter: this.state.filter,
      doSpin: true,
      partial: ''
    }, () => {
      let http = new Http($.ajax);
      let url = filter_model.createUrl().getUrl();

      http.sendGet(url).then(r => {
        let state = {
          filter: filter_model,
          partial: r.getData(),
          doSpin: false
        };
        if(state.doSpin !== false) { throw new Error('wtf') };
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
                        updateFilter={this.updateFilter.bind(this)}
                        doSpin={this.state.doSpin}/>
        <FeedbackResults filterModel={this.state.filter}
                         partial={this.state.partial}
                         updateFilter={this.updateFilter.bind(this)}
                         doSpin={this.state.doSpin} />
      </article>
    );
  }
}
