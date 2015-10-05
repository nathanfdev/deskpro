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

class SuggestionMore extends React.Component {
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
    return (
      <li>
        <a
          onClick={this.props.showAll}
          onMouseOver={this.onFocus.bind(this)}
          onMouseOut={this.onBlur.bind(this)}
          className={(this.state.focused ? 'focus' : '') + (this.props.alt ? ' alt' : '')}
          >
          Show <strong>{this.props.count}</strong> more...
        </a>
      </li>
    );
  }
}


class Suggestions extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
        show_all: false
    };
  }
  showMore() {
    this.setState({
      show_all: true
    });
  }
  componentWillReceiveProps(newProps) {
    if (this.props.results !== newProps.results) {
      this.setState({
        show_all: false
      });
    }
  }
  render() {
    if (this.props.results.length === 0) {
      return null;
    }
    let visible_results;

    if (!this.state.show_all) {
      visible_results = _.slice(this.props.results, 0, 5);
    } else {
      visible_results = this.props.results;
    }
    return (
      <div>
        <hr/>
        <div className="result-list">
          <ul>
            {
              _.map(visible_results, (result, idx) => {
                return (
                  <SuggestionRow key={result.type + result.object.id} alt={idx % 2 === 0} result={result}/>
                );
              })
            }
            {
              (!this.state.show_all && this.props.results.length > 5) ? (<SuggestionMore alt={visible_results.length % 2 === 0} count={this.props.results.length - 5} showAll={this.showMore.bind(this)} />) : null
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
        content: ''
      }
    };
  }
  componentDidMount() {
    let throttleChanges = _.throttle((e) => {
      this.doSearch({ content: e.target.value });
    }, 250);
    this.state.$input.on('keyup', throttleChanges);
  }
  doSearch(query_modifications) {
    const last_query = this.state.search_query || {};
    const search_query = {...last_query, ...query_modifications};
    this.setState({
      search_query
    });

    if (!search_query.content || search_query.content.length < 3) {
      // we need a query with a length of at least 3 for the server to do any real searching
      // so don't do a HTTP request if we don't at least have that
      return;
    }

    this.setState({
      doSpin: true
    });

    PortalHttp.sendGet('DP_URL/search/similar', { data: search_query }).then((r) => {
      if (!r.isError()) {
        this.setState({
          data: r.data.data,
          search_query,
          doSpin: false
        });
      }
    });
  }
	render() {
    let data = this.state.data;
    return (
      <div style={{"display": (this.state.search_query.content.length >= 3 && data.results.length > 0 ? " block" : "none")}}>
        <div className="search-box-results">
          <header>
            <span className="result-count">We found the following content that may answer your question</span>
            <img style={{display: this.state.doSpin ? "inline" : "none", height: "18px", width: "18px", marginLeft: "3px"}}
                 src={ PortalUrlGenerator.getSpinnerPath() }/>
          </header>
          <Suggestions results={data.results} />
        </div>
      </div>
    );
  }
}

