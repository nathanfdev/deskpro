import React, { Component, PropTypes } from 'react';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { CollectionField } from '../../../../Common/Components/Popup';
import { DepartmentsList } from './DepartmentsList';

import { connect } from 'react-redux';
@connect(state => ({
  departments: allSelectorFactory('Department')(state)
}))

export class DepartmentsListContainer extends Component {
  static propTypes = {
    onClick: PropTypes.func.isRequired,
    departments: PropTypes.object.isRequired,
    multiple: PropTypes.bool,
    showOnlySelected: PropTypes.bool,
    filter: PropTypes.string,
    selected: PropTypes.number
  };

  render() {
    const { selected, departments, onClick, filter, multiple, showOnlySelected } = this.props;

    return (
      <CollectionField title="Department">
        <DepartmentsList param="department"
                         values={departments}
                         selected={selected}
                         multiple={multiple}
                         showOnlySelected={showOnlySelected}
                         filter={filter}
                         onClick={onClick}/>
      </CollectionField>
    );
  }
}
