import React from "react"
import _ from "lodash"
import $ from "jquery"
import PortalHttp from "DeskPRO/Bundle/PortalBundle/Http/PortalHttp"
import PortalUrlGenerator from "DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator"
import OmniSearchResults from "DeskPRO/Bundle/PortalBundle/React/OmniSearch/OmniSearchResults"

class PaginationLink extends React.Component {
  onClick() {
    this.props.onClick(this.props.page);
  }
  render() {
    return (<li><a onClick={this.onClick.bind(this)}>{this.props.text}</a></li>);
  }
}

class Pagination extends React.Component {
  render() {
    const current = _.parseInt(this.props.currentPage);
    const total = _.parseInt(this.props.totalResults);
    const per_page = _.parseInt(this.props.perPageResults);
    const onClick = this.props.pageClick;
    const pages = Math.ceil(total / per_page);

    if (pages <= 1) {
      // don't show a pager if its the only page
      return null;
    }

    let nextLink = (<li className="inactive"><a href="#">Next</a></li>);
    if (current < pages) {
      nextLink = (<PaginationLink onClick={this.props.pageClick} page={current+1} text="Next"/>);
    }

    let prevLink = (<li className="inactive"><a href="#">Back</a></li>);
    if (current > 1) {
      prevLink = (<PaginationLink onClick={this.props.pageClick} page={current-1} text="Back"/>);
    }

    let prevPaddings = [];
    if (current > 1) {
      prevPaddings.push((<PaginationLink key={'prev'+(current-1)} onClick={this.props.pageClick} page={current - 1} text={current - 1}/>));
      if (current > 2) {

        prevPaddings.unshift((<PaginationLink key={'prev'+(current-2)} onClick={this.props.pageClick} page={current - 2} text={current - 2}/>));
      }
    }

    let nextPaddings = [];
    if (current < pages) {
      nextPaddings.push((<PaginationLink key={'next'+(current+1)} onClick={this.props.pageClick} page={current + 1} text={current + 1}/>));
      if ((current + 1) < pages) {

        nextPaddings.push((<PaginationLink key={'next'+(current+2)} onClick={this.props.pageClick} page={current + 2} text={current + 2}/>));
      }
    }

    return (
      <ul className="pagination">
        {prevLink}
        {prevPaddings}
        <li className="active-page"><a href="#">{current}</a></li>
        {nextPaddings}
        {nextLink}
      </ul>
    );
  }
}

export default class OmniSearch extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      doSpin: false,
      $input: $(props.input),
      data: {
        pageinfo: {
          total_results: 0,
          curpage: 1
        }
      },
      search_query: null
    };
  }
  componentDidMount() {
    let throttleChanges = _.throttle((e) => {
      this.doSearch({ q: e.target.value });
    }, 250);
    this.state.$input.on('keyup', throttleChanges);
  }
  doSearch(query_modifications) {
    const last_query = this.state.search_query || {};
    const search_query = {...last_query, ...query_modifications};
    this.setState({
      search_query
    });

    console.log("search query: %o", search_query);

    if (!search_query.q || search_query.q.length < 3) {
      // we need a query with a length of at least 3 for the server to do any real searching
      // so don't do a HTTP request if we don't at least have that
      return;
    }

    this.setState({
      doSpin: true
    });

    PortalHttp.sendGet('/search', { data: search_query }).then((r) => {
      console.log("response %o", r);
      if (!r.isError()) {
        this.setState({
          data: r.data.data,
          search_query,
          doSpin: false
        });
      }
    });
  }
  changePage(page) {
    this.doSearch({page: page});
  }
	render() {
    let data = this.state.data;
    let total = _.parseInt(data.pageinfo.total_results);
      return (
        <div className="live-results-container">
          <div className="search-box-results">
            <header>
              <span className="result-count">{total} results</span>
              <img style={{display: this.state.doSpin ? "inline" : "none", height: "18px", width: "18px", marginLeft: "3px"}}
                   src={ window.DESKPRO_BASE_URL + '/web/spinner.gif' }/>
              <ul className="result-filter">
                <li><a href="#"><i className="fa fa-check"></i> Downloads</a></li>

                <li><a href="#"><i className="fa fa-check"></i> Articles</a></li>

                <li><a href="#"><i className="fa fa-check"></i> News</a></li>

                <li><a href="#"><i className="fa fa-check"></i> Feedback</a></li>
              </ul>
            </header>
            <OmniSearchResults total={total} results={data.results} />
            <Pagination
              currentPage={data.pageinfo.curpage}
              totalResults={total}
              perPageResults={data.pageinfo.per_page}
              pageClick={this.changePage.bind(this)}
              />
            <hr />
            <div className="live-search-meta">
              <div>Still haven't found what you're looking for?</div>
              <a href={PortalUrlGenerator.path('/new-ticket')} className="button">Contact Us</a>
              <a href={PortalUrlGenerator.path('/feedback')} className="button">Submit Feedback</a>
              <a href="#" className="button">Start Chat Session</a>
            </div>
          </div>
        </div>
      );
    }
}

