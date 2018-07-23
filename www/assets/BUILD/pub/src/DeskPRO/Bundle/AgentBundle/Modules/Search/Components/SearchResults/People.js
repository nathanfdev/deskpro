import React from 'react';
import PropTypes from 'prop-types';
import { FormattedMessage } from 'react-intl';
import { Icon } from '@deskpro/react-components';

export default class People extends React.Component {
  static propTypes = {
    people:   PropTypes.array,
    maxItems: PropTypes.number,
  };

  static defaultProps = {
    maxItems: 3
  };

  static getAvatar(person) {
    return <img className="avatar" alt="avatar" src={person.img} />;
  }

  static renderPeople(people) {
    return people.map(person =>
      <div key={person.id} className="person">
        {People.getAvatar(person)}
        <span className="title">{person.name}</span>
        <span className="email">{person.email}</span>
        <span className="tickets-count">
          <Icon name="envelope-o" size="m" />
          {person.tickets}
        </span>
        <span className="new-ticket">
          <Icon name="envelope-o" size="m" />
        </span>
      </div>
    );
  }

  constructor(props) {
    super(props);
    this.state = {
      expanded: false
    };
  }

  showMore = () => {
    this.setState({
      expanded: true
    });
  };

  renderCollapsed() {
    const { people, maxItems } = this.props;
    const collapsed = People.renderPeople(people.slice(0, maxItems));
    collapsed.push(
      <div className="expand" key="expand" onClick={this.showMore}>
        <FormattedMessage id="agent.general.show_x_more" values={{ count: people.length - maxItems }} />
      </div>
    );
    return collapsed;
  }

  render() {
    const { people, maxItems } = this.props;
    return (
      <section className="people">
        <header><h1>People</h1></header>
        { (people.length <= maxItems || this.state.expanded) ?
          People.renderPeople(people)
          : this.renderCollapsed()
        }
      </section>
    );
  }
}
