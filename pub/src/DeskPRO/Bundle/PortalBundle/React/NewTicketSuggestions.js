import React from 'react';
import _ from 'lodash';
import $ from 'jquery';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

class SuggestionRow extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    const type = this.props.result.type;
    const icon = this.props.result.object.icon_html;

    return (
      <li className="related-article-item">
        <a
          className="related-article-link"
          href={this.props.result.object.url}
          target="_blank"
          >
          <span dangerouslySetInnerHTML={{__html: icon}}></span>
          <span className="item-title">{this.props.result.object.name}</span>
        </a>
      </li>
    );
  }
}

class SuggestionMore extends React.Component {
  constructor(props) {
    super(props);
  }

  render() {
    return (
      <li className="related-list-meta">
        <a
          onClick={this.props.showAll}
          className="show-more-content"
          >
          {portalPhrases.get('portal.general.show_x_more', {num: this.props.count})}
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
    const throttleChanges = _.throttle((e) => {
      this.doSearch({ content: e.target.value });
    }, 250);
    this.state.$input.on('keyup', throttleChanges);
  }

  doSearch(queryModifications) {
    const lastQuery = this.state.search_query || {};
    const search_query = {...lastQuery, ...queryModifications};
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

    portalHttp.sendGet('DP_URL/search/similar', { data: search_query }).then((r) => {
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
    const data = this.state.data || [];
    const results = data.results || [];

    return (
      <div style={{display: (this.state.search_query.content.length >= 3 && results.length > 0 ? ' block' : 'none')}}>
        <div className="ticket-related-articles">
          <header>
            <h1>{portalPhrases.get('portal.tickets.related_articles_title')}</h1>
            <h2>{portalPhrases.get('portal.tickets.related_articles_desc')}</h2>
          </header>
          <Suggestions results={results} />
        </div>
      </div>
    );
  }
}
