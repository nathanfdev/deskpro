import PropTypes from 'prop-types';
import React from 'react';
import debounce from 'lodash/debounce';
import map from 'lodash/map';
import slice from 'lodash/slice';
import Isvg from 'react-inlinesvg';
import $ from 'jquery';
import SuggestionsSvg from '@deskpro/portal-style/dist/img/page-icons/ticket-suggestions.svg';
import { portalHttp } from 'DeskPRO/Bundle/PortalBundle/Http/PortalHttp';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';
import { FormattedMessage, injectIntl } from 'react-intl';

class SuggestionRow extends React.Component {

  static propTypes = {
    result: PropTypes.object
  };

  render() {
    const icon = this.props.result.object.icon_html;

    return (
      <li className="dp-po-ticket-create-suggestions-item">
        <a
          className="dp-po-ticket-create-suggestions-link"
          href={this.props.result.object.url}
          target="_blank"
          rel="noopener noreferrer"
        >
          <span dangerouslySetInnerHTML={{ __html: icon }} />&nbsp;
          <span className="dp-po-ticket-create-suggestions-text">{this.props.result.object.name}</span>
        </a>
      </li>
    );
  }
}

class SuggestionMore extends React.Component {

  static propTypes = {
    showAll: PropTypes.func,
    count:   PropTypes.number
  };

  render() {
    const { showAll, count } = this.props;

    return (
      <span className="dp-po-ticket-create-suggestions-more">
        <a
          onClick={showAll}
          style={{ textDecoration: 'underline', cursor: 'pointer', fontWeight: 700 }}
        >
          <FormattedMessage id="helpcenter.general.show_count_more" values={{ count }} />
        </a>
      </span>
    );
  }
}

class SuggestionLess extends React.Component {

  static propTypes = {
    showLess: PropTypes.func
  };

  render() {
    return (
      <span className="dp-po-ticket-create-suggestions-more">
        <a
          onClick={this.props.showLess}
          style={{ textDecoration: 'underline', cursor: 'pointer', fontWeight: 700 }}
        >
          {portalPhrases.get('portal.general.show_less')}
        </a>
      </span>
    );
  }
}


class Suggestions extends React.Component {

  static propTypes = {
    results: PropTypes.array
  };

  constructor(props) {
    super(props);
    this.state = {
      show_all: false
    };
  }

  componentWillReceiveProps(newProps) {
    if (this.props.results !== newProps.results) {
      this.setState({
        show_all: false
      });
    }
  }

  showMore = () => {
    this.setState({
      show_all: true
    });
  };

  showLess = () => {
    this.setState({
      show_all: false
    });
  };

  render() {
    const { results } = this.props;
    if (results.length === 0) {
      return null;
    }

    let visibleResults;
    if (!this.state.show_all) {
      visibleResults = slice(results, 0, 5);
    } else {
      visibleResults = results;
    }

    return (
      <div>
        <ul className="dp-po-ticket-create-suggestions-list"  style={{ marginBottom: 0 }}>
          { map(visibleResults, (result, idx) =>
            <SuggestionRow key={result.type + result.object.id} alt={idx % 2 === 0} result={result} />)}
        </ul>
        {
          (!this.state.show_all && results.length > 5) ? (<SuggestionMore alt={visibleResults.length % 2 === 0} count={results.length - 5} showAll={this.showMore} />) : null
        }
        {
          (this.state.show_all && results.length > 5) ? (<SuggestionLess alt={visibleResults.length % 2 === 0} count={results.length - 5} showLess={this.showLess} />) : null
        }
      </div>
    );
  }
}

class HelpcenterNewTicketSuggestions extends React.Component {

  static propTypes = {
    input: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      doSpin: false,
      $input: $(props.input),
      data:   {
        results: [],
        words:   []
      },
      search_query: {
        content: ''
      }
    };
  }

  componentDidMount() {
    const debounceChanges = debounce((e) => {
      this.doSearch({ content: e.target.value });
    }, 500);
    this.state.$input.on('keyup', debounceChanges);
  }

  doSearch(queryModifications) {
    const lastQuery = this.state.search_query || {};
    const searchQuery = { ...lastQuery, ...queryModifications };
    this.setState({
      search_query: searchQuery
    });

    if (!searchQuery.content || searchQuery.content.length < 3) {
      // we need a query with a length of at least 3 for the server to do any real searching
      // so don't do a HTTP request if we don't at least have that
      return;
    }

    this.setState({
      data:   [],
      doSpin: true
    });

    portalHttp.sendGet('DP_URL/search/similar/article', { data: searchQuery }).then((r) => {
      if (!r.isError()) {
        this.setState({
          data:         r.data.data,
          search_query: searchQuery,
          doSpin:       false
        });
      }
    });
  }

  render() {
    const { data, search_query } = this.state;
    const results = data && data.results ? data.results : [];

    if (search_query.content.length < 3 || !results.length) {
      return null;
    }

    return (
      <div>
        <div className="dp-po-block dp-po-ticket-create-suggestions" style={{ marginTop: '10px' }}>
          <header>
            <div className="dp-po-ticket-create-suggestions-title">
              <Isvg src={SuggestionsSvg} className="dp-po-ticket-create-suggestions-image" />
              <FormattedMessage id="helpcenter.tickets.related_articles_desc" />
            </div>
          </header>
          <Suggestions results={results} />
        </div>
      </div>
    );
  }
}

export default injectIntl(HelpcenterNewTicketSuggestions);
