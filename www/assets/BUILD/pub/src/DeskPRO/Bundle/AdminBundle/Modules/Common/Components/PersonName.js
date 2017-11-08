import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { allPeopleSelector } from '../../Application/Selectors/people';

@connect(state => ({
  people: allPeopleSelector(state)
}))
class PersonNameContainer extends React.Component {

  render() {
    return <PersonName {...this.props} />;
  }
}

class PersonName extends React.Component {

  static propTypes = {
    id:        PropTypes.number,
    people:    PropTypes.object,
    className: PropTypes.string
  };

  render() {
    const { id, people, className } = this.props;
    if (!id) {
      return '';
    }

    const person = people.get(id);
    const email = person && person.get('primary_email');

    let name = person && person.get('name') ? person.get('name') : `ID-${id}`;
    if (email) {
      name += ` <${email}>`;
    }

    return (
      <span className={className}>
        {name}
      </span>
    );
  }
}

export default PersonNameContainer;
