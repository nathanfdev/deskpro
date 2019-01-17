import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import { Organizations, People, Publishing, Tickets } from './SearchResults/';

class SearchResults extends React.Component {
  static propTypes = {
    results: PropTypes.object,
    scopes:  PropTypes.array,
  };

  static defaultProps = {
    scopes:  [],
    results: {},
  };

  constructor(props) {
    super(props);
    this.state = {
      activePane: 'helpdesk'
    };
  }

  handleTabChange = (a) => {
    if (a !== this.state.activePane) {
      this.setState({
        activePane: a
      });
    } else {
      this.setState({
        activePane: 'helpdesk'
      });
    }
  };

  renderOrganizations = () => {
    const { results } = this.props;
    if (!results.organizations || results.organizations.length === 0) {
      return null;
    }
    return <Organizations organizations={results.organizations} />;
  };

  renderPeople = () => {
    const { results } = this.props;
    if (!results.people || results.people.length === 0) {
      return null;
    }
    return <People people={results.people} />;
  };

  renderTickets = () => {
    const { results } = this.props;
    if (!results.tickets || results.tickets.length === 0) {
      return null;
    }
    return <Tickets tickets={results.tickets} />;
  };

  renderPublishing = () => {
    const { results } = this.props;
    if (!results.tickets || results.tickets.length === 0) {
      return null;
    }
    return <Publishing results={results} />;
  };

  render() {
    const { scopes } = this.props;
    console.log(scopes);
    const scope = (scopes.length === 0 || scopes.length > 1) ? 'global' : scopes[0];
    console.log(scope);
    switch (scope) {
      case 'Ticket':
        return (
          <div id="dp_search_results">
            <h1><FormattedMessage id="agent.general.search_results" /></h1>
            {this.renderTickets()}
          </div>
        );
      case 'Person':
        return (
          <div id="dp_search_results">
            <h1><FormattedMessage id="agent.general.search_results" /></h1>
            {this.renderPeople()}
          </div>
        );
      case 'Organization':
        return (
          <div id="dp_search_results">
            <h1><FormattedMessage id="agent.general.search_results" /></h1>
            {this.renderOrganizations()}
          </div>
        );
      case 'Content':
        return (
          <div id="dp_search_results">
            <h1><FormattedMessage id="agent.general.search_results" /></h1>
            {this.renderPublishing()}
          </div>
        );
      case 'global':
      default:
        return (
          <div id="dp_search_results">
            <h1><FormattedMessage id="agent.general.search_results" /></h1>
            {this.renderOrganizations()}
            {this.renderPeople()}
            {this.renderTickets()}
            {this.renderPublishing()}
          </div>
        );
    }
  }
}
export default SearchResults;
