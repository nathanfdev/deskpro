import React from "react"
import _ from "lodash"
import $ from "jquery"
import PortalHttp from "DeskPRO/Bundle/PortalBundle/Http/PortalHttp"
import PortalUrlGenerator from "DeskPRO/Bundle/PortalBundle/Http/PortalUrlGenerator"

class SuggestionRow extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      focused: false
    }
  }

  onFocus() {
    this.setState({
      focused: true
    });
  }

  onBlur() {
    this.setState({
      focused: false
    })
  }

  render() {
    const type = this.props.result.type;
    let icon;
    switch (type) {
      case 'download':
        icon = (<i className="fa fa-download"></i>);
        break;
      default:
        icon = (<i className="fa fa-file-text-o"></i>);
    }
    return (
      <li>
        <a
          href={this.props.result.object.url}
          onMouseOver={this.onFocus.bind(this)}
          onMouseOut={this.onBlur.bind(this)}
          className={(this.state.focused ? 'focus' : '') + (this.props.alt ? ' alt' : '')}
          >
          {icon}
          <span className="text-tag">
            {type.toUpperCase()}
          </span>
          {this.props.result.object.name}
        </a>
      </li>
    );
  }
}

class Suggestions extends React.Component {
  render() {
    if (this.props.results.length === 0) {
      return null;
    }

    return (
      <div>
        <hr/>
        <div className="result-list">
          <ul>
            {
              _.map(this.props.results, (result, idx) => {
                return (
                  <SuggestionRow key={result.type + result.object.id} alt={idx % 2 === 0} result={result}/>
                );
              })
            }
          </ul>
        </div>
      </div>
    );
  }
}

export default class NewTicketSuggestions extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      doSpin: false,
      $input: $(props.input),
      data: {
        results: [],
        words: []
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

    PortalHttp.sendGet('/search/similar', { data: search_query }).then((r) => {
      if (!r.isError()) {
        this.setState({
          data: r.data.data,
          search_query,
          doSpin: false
        });
      }
    }).catch((r) => {
      console.log('caught error: %o', r);
    });
  }
	render() {
    let data = this.state.data;
      return (
        <div className={"live-results-container" + (this.state.search_query.q.length > 3 ? " show" : "")}>
          <div className="search-box-results">
            <header>
              <span className="result-count">We found the following content that may answer your question</span>
              <img style={{display: this.state.doSpin ? "inline" : "none", height: "18px", width: "18px", marginLeft: "3px"}} />
            </header>
            <Suggestions results={data.results} />
          </div>
        </div>
      );
    }
}

