import React from 'react';
import PropTypes from 'prop-types';
import { Section } from '@deskpro/react-components';
import { Organizations, People, Publishing, Tickets, TopTabs } from './SearchResults/';

class SearchResults extends React.Component {
  static propTypes = {
    results: PropTypes.object
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

  render() {
    const { results } = this.props;
    const { activePane } = this.state;
    return (
      <div id="dp_search_results">
        <TopTabs active={activePane} results={results} onChange={this.handleTabChange} />
        <Section hidden={activePane !== 'publishing'}>
          <Publishing results={results} />
        </Section>
        <Section hidden={activePane !== 'admin'}>
          Admin
        </Section>
        {this.renderOrganizations()}
        {this.renderPeople()}
        {this.renderTickets()}
      </div>
    );
  }
}
export default SearchResults;
