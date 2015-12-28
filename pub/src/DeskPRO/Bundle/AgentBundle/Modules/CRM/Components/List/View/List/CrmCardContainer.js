import React, {Component, PropTypes} from 'react';
import { CrmCard } from './CrmCard';
import { currentListParamsSelector } from '../../../../Selectors/list';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    people: state.CRM.list.get('people'),
    organizations: state.CRM.list.get('organizations'),
    selected: state.CRM.list.get('selected'),
    listParams: currentListParamsSelector(state)
  });
})

export class CrmCardContainer extends Component {
  static propTypes = {
    people: PropTypes.object,
    organizations: PropTypes.object,
    listParams: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired
  };

  render() {
    const { people, organizations, selected, listParams } = this.props;
    const elements = listParams.get('content') === 'organizations' ? organizations : people;
    return (
      <div>
        {elements.map((element, index) =>
            <CrmCard key={index}
                     element={element}
                     selected={selected.includes(element.id)}/>
        )}
      </div>);
  }
}