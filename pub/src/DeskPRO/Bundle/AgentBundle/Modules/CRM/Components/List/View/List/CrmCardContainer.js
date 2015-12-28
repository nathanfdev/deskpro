import React, {Component, PropTypes} from 'react';
import { CrmCard } from './CrmCard';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    people: state.CRM.list.get('people'),
    selected: state.CRM.list.get('selected')
  });
})

export class CrmCardContainer extends Component {
  static propTypes = {
    people: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired
  };

  render() {
    const { people, selected } = this.props;
    return (
      <div>
        {people.map((element, index) =>
            <CrmCard key={index}
                     element={element}
                     selected={selected.includes(element.id)}/>
        )}
      </div>);
  }
}