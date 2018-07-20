import React from 'react';
import PropTypes from 'prop-types';
import { Icon } from '@deskpro/react-components';

export default class People extends React.Component {
  static propTypes = {
    people: PropTypes.array
  };

  static getAvatar(person) {
    return <img className="avatar" alt="avatar" src={person.img} />;
  }

  render() {
    const { people } = this.props;
    return (
      <section className="people">
        <header><h1>People</h1></header>
        {people.map(person =>
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
        )}
      </section>
    );
  }
}
