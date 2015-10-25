import React from "react"
import _ from "lodash"
import $ from "jquery"
import PortalHttp from "DeskPRO/Bundle/PortalBundle/Http/PortalHttp"
import PortalUrlGenerator from "DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator"
import OmniSearchResults from "DeskPRO/Bundle/PortalBundle/React/OmniSearch/OmniSearchResults"
import Pagination from "DeskPRO/Bundle/PortalBundle/React/Pagination"

class SearchType extends React.Component {
  toggle() {
    this.props.toggleType(this.props.type);
  }
  render() {
    return (
      <li><a className="omnisearch-type" onClick={this.toggle.bind(this)}>{this.props.active ? <i className="fa fa-check"></i> : null} {this.props.name}</a></li>
    );
  }
}

export default class OmniSearch extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      doSpin: false,
      $input: $(props.input),
      $close: $(props.close),
      data: {
        pageinfo: {
          total_results: 0,
          curpage: 1
        }
      },
      search_query: {
        q: ''
      },
      types: [
        {type: 'download', name: 'Downloads', active: true},
        {type: 'article', name: 'Articles', active: true},
        {type: 'news', name: 'News', active: true},
        {type: 'feedback', name: 'Feedback', active: true}
      ]
    };
  }
  componentDidMount() {
    let throttleChanges = _.throttle((e) => {
      this.doSearch({ q: e.target.value });
    }, 250);
    this.state.$input.on('keyup', throttleChanges);
    this.state.$close.click(() => {
      this.state.$input.val('');
      this.doSearch({ q: '' }); // reset/close search
    });
  }
  doSearch(query_modifications) {
    const last_query = this.state.search_query || {};
    const search_query = {...last_query, ...query_modifications};
    this.setState({
      search_query
    });

    if (!search_query.q || search_query.q.length < 3) {
      // we need a query with a length of at least 3 for the server to do any real searching
      // so don't do a HTTP request if we don't at least have that
      return;
    }

    this.setState({
      doSpin: true
    });

    PortalHttp.sendGet('DP_URL/search', { data: search_query }).then((r) => {
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
        <div className="expanded-search-results" style={{display: this.state.search_query.q.length > 0 ? "block" : "none"}}>
          <div className="search-result-collection">
            <h1><i className="fa fa-file-text-o"></i> Knowledge base</h1>
            <ul>
              <li><a href="#">Keep Files On Your Users (Literally)</a></li>
              <li><a href="#">Can I keep my existing support email address?</a></li>
              <li><a href="#">Quick Start Guide for new agents</a></li>
              <li><a href="#">Keeping read mail on the POP3 server</a></li>
            </ul>
            <a href="#" className="search-results-show-more">10 More <i className="fa fa-angle-double-down"></i></a>
          </div>

          <div className="search-result-collection">
            <h1><i className="fa fa-download"></i> Downloads</h1>
            <ul>
              <li><a href="#">New Feature: Keep Files On Your Users (Literally)</a></li>
              <li><a href="#">Can I keep my existing support email address?</a></li>
            </ul>
            <a href="#" className="search-results-show-more">10 More <i className="fa fa-angle-double-down"></i></a>
          </div>

          <div className="search-result-collection">
            <h1><i className="fa fa-file-text-o"></i> News</h1>
            <ul>
              <li><a href="#"><span className="date-mark"><i className="fa fa-calendar-o"></i> 10 days ago</span> New Feature:
                Keep <span className="search-match-mark">Files On Your</span> Users (Literally)</a></li>
              <li><a href="#"><span className="date-mark"><i className="fa fa-calendar-o"></i> 10 days ago</span> Can I keep my
                existing support email address?</a></li>
            </ul>
            <a href="#" className="search-results-show-more">10 More <i className="fa fa-angle-double-down"></i></a>
          </div>

          <div className="search-result-collection">
            <h1><i className="fa fa-comments"></i> Feedback</h1>
            <ul>
              <li><a href="#"><span className="feedback-mark"><i className="fa fa-thumbs-up"></i> +12</span> New Feature: Keep
                Files On Your Users (Literally)</a></li>
              <li><a href="#"><span className="feedback-mark"><i className="fa fa-thumbs-up"></i> +12</span> Can I keep my
                existing support email address?</a></li>
            </ul>
            <a href="#" className="search-results-show-more">10 More <i className="fa fa-angle-double-down"></i></a>
          </div>

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
      return (
        <div className={"live-results-container" + (this.state.search_query.q.length > 0 ? " show" : "")}>
          <div className="search-box-results">
            <header>
              <span className="result-count">{total} results</span>
              <img style={{display: this.state.doSpin ? "inline" : "none", height: "18px", width: "18px", marginLeft: "3px"}}
                src={ PortalUrlGenerator.getSpinnerPath() }/>
              <ul className="result-filter">
                {_.map(this.state.types, (type) => {
                  return (<SearchType key={type.type} name={type.name} active={type.active} type={type.type} toggleType={this.toggleType.bind(this)} />);
                })}
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

