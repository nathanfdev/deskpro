import React, { Component, PropTypes } from 'react';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { paramsSelector } from '../../../../Application/Selectors/massActions';
import { CollectionField } from '../../../../Common/Components/Popup';
import { DepartmentsList } from './DepartmentsList';

import { connect } from 'react-redux';
@connect(state => ({
  departments: allSelectorFactory('AgentTeam')(state),
  currentParams: paramsSelector(state)
}))

export class DepartmentsListContainer extends Component {
  static propTypes = {
    onChange: PropTypes.func.isRequired,
    departments: PropTypes.object.isRequired,
    currentParams: PropTypes.object
  };

  render() {
    const { currentParams, departments, onChange } = this.props;
    const assign = currentParams.get('assign');

    return (
      <CollectionField title="Department">
        <DepartmentsList values={departments}
                         selected={assign && assign.get('department')}
                         onChange={onChange}/>
      </CollectionField>
    );
  }
}
