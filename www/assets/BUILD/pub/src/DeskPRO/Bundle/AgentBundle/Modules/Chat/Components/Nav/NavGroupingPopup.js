import React, { Component, PropTypes } from 'react';
import { ListGroupingControlContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { changeListGrouping } from '../../Actions/chatNavActions.js';

export class NavGroupingPopup extends Component {

  static propTypes = {
    dispatch:                PropTypes.func.isRequired,
    closeGroupingVisibility: PropTypes.func.isRequired,
    content:                 PropTypes.string.isRequired,
    groupedBy:               PropTypes.string.isRequired,
    attachTo:                PropTypes.object.isRequired,
    visible:                 PropTypes.bool.isRequired
  };

  static groupingOptions = {
    my: [
      { value: 'date_period', label: 'Date Created' },
      { value: 'department', label: 'Department' }
    ],

    all: [
      { value: 'agent', label: 'Agent' },
      { value: 'department', label: 'Department' },
      { value: 'date_period', label: 'Date Created' }
    ]
  };

  render() {
    const { attachTo, content, groupedBy, visible, closeGroupingVisibility } = this.props;
    const options  = NavGroupingPopup.groupingOptions;
    const selected = options[content].find(item => item.value === groupedBy);

    return (
      <ListGroupingControlContainer
        visible={visible}
        content={content}
        options={options[content]}
        changeListGrouping={changeListGrouping}
        closeGroupingVisibility={closeGroupingVisibility}
        selected={selected.value}
        attachTo={attachTo}
      />
    );
  }
}
