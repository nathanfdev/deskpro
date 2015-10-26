import React from "react";
import _ from "lodash";
import $ from "jquery";
import PortalHttp from "DeskPRO/Bundle/PortalBundle/Http/PortalHttp";
import PortalUrlGenerator from "DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator";
import OmniSearchResultSection from "DeskPRO/Bundle/PortalBundle/React/OmniSearch/OmniSearchResultSection";
import Pagination from "DeskPRO/Bundle/PortalBundle/React/Pagination";

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
      }
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

    PortalHttp.sendGet('DP_URL/search/omni', { data: search_query }).then((r) => {
      if (!r.isError()) {
        this.setState({
          data: r.data.data,
          search_query,
          doSpin: false
        });
      }
    });
  }
  doResultsExist() {
    let grandTotal = 0;
    _.forOwn(this.state.data, (type_results, type) => {
      if ("results" in type_results) {
        grandTotal += _.keys(type_results.results).length;
      }
    });

    return grandTotal > 0;
  }
	render() {
    let data = this.state.data;

    if (this.state.search_query.q.length < 3) {
      return null;
    }

    return (
        <div className="expanded-search-results" style={{
          display: this.state.search_query.q.length > 0 ? "block" : "none",
          width: this.state.$input.closest('.search-form').width()
        }}>
          {
            this.doResultsExist() ?
              (<div>
                <OmniSearchResultSection
                  name="Knowledge base"
                  nameApi="article"
                  nameIcon="fa fa-file-text-o"
                  initialResult={"article" in data ? data.article : []}
                  q={this.state.search_query.q}
                  />

                <OmniSearchResultSection
                  name="Downloads"
                  nameApi="download"
                  nameIcon="fa fa-download"
                  initialResult={"download" in data ? data.download : []}
                  q={this.state.search_query.q}
                  />

                <OmniSearchResultSection
                  name="News"
                  nameApi="news"
                  nameIcon="fa fa-file-text-o"
                  initialResult={"news" in data ? data.news : []}
                  q={this.state.search_query.q}
                  />

                <OmniSearchResultSection
                  name="Feedback"
                  nameApi="feedback"
                  nameIcon="fa fa-comments"
                  initialResult={"feedback" in data ? data.feedback : []}
                  q={this.state.search_query.q}
                  />
              </div>) :
              (<div>
                <h1>No Results</h1>
              </div>)
          }

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
    }
}

