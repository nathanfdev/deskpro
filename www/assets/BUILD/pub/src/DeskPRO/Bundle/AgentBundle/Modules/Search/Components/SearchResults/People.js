import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import classNames from 'classnames';
import { faEnvelope } from '@fortawesome/free-regular-svg-icons';
import { Icon } from '@deskpro/react-components';
import PersonTickets from './PersonTickets';

export default class People extends React.Component {
  static propTypes = {
    people:   PropTypes.array,
    maxItems: PropTypes.number,
  };

  static defaultProps = {
    maxItems: 3
  };

  static getAvatar(person) {
    return <img className="avatar" src={person.img} role="presentation" />;
  }

  constructor(props) {
    super(props);
    this.state = {
      expanded:      false,
      personTickets: null,
    };
  }

  showMore = () => {
    this.setState({
      expanded: true
    });
  };

  showPersonTickets = (personId) => {
    if (this.state.personTickets === personId) {
      this.setState({
        personTickets: null
      });
    } else {
      this.setState({
        personTickets: personId
      });
    }
  };

  renderCollapsed() {
    const { people, maxItems } = this.props;
    const collapsed = this.renderPeople(people.slice(0, maxItems));
    collapsed.push(
      <div className="expand" key="expand" onClick={this.showMore}>
        <FormattedMessage id="agent.general.show_x_more" values={{ count: people.length - maxItems }} />
      </div>
    );
    return collapsed;
  }

  renderPeople(people) {
    const result = [];
    people.forEach((person) => {
      result.push(
        <div key={person.id} className={classNames('person', { expanded: person.id === this.state.personTickets })}>
          {People.getAvatar(person)}
          <span className="title">{person.name}</span>
          <span className="email">{person.email}</span>
          <a className="tickets-count" onClick={() => this.showPersonTickets(person.id)}>
            <Icon name={faEnvelope} size="m" />
            <span className="count-label">{person.tickets}</span>
          </a>
          <span className="new-ticket">
            <Icon name={faEnvelope} size="m" />
          </span>
        </div>
      );
      if (person.id === this.state.personTickets) {
        result.push(
          <PersonTickets className="person-tickets" key={`person-tickets-${person.id}`} personId={person.id} />
        );
      }
    });
    return result;
  }

  render() {
    const { people, maxItems } = this.props;
    return (
      <section className="people">
        <header><h1>People</h1></header>
        { (people.length <= maxItems || this.state.expanded) ?
          this.renderPeople(people)
          : this.renderCollapsed()
        }
      </section>
    );
  }
}
