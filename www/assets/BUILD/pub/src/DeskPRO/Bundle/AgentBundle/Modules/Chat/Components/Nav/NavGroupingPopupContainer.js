import React, { Component, PropTypes } from 'react';
import { ListGroupingControl } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { changeListGrouping } from '../../Actions/chatNavActions.js';
import { connect } from 'react-redux';
@connect()

export class NavGroupingPopupContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    closeGroupingVisibility: PropTypes.func.isRequired,
    content: PropTypes.string.isRequired,
    groupedBy: PropTypes.string.isRequired,
    attachTo: PropTypes.object.isRequired,
    visible: PropTypes.bool.isRequired
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

  applyFilterEditing = (e) => {
    const options = e.target.options;
    const {dispatch, content, closeGroupingVisibility} = this.props;
    for (let i = 0; i < options.length; i++) {
      if (options[i].selected) {
        dispatch(changeListGrouping(content, options[i].value));
        break;
      }
    }
    closeGroupingVisibility();
  };

  render() {
    const { attachTo, content, groupedBy, visible, closeGroupingVisibility } = this.props;
    const selected = NavGroupingPopupContainer.groupingOptions[content].find(item => item.value === groupedBy);

    return (
      <ListGroupingControl visible={visible}
                           title={content.charAt(0).toUpperCase() + content.slice(1)}
                           options={NavGroupingPopupContainer.groupingOptions[content]}
                           onChange={this.applyFilterEditing}
                           close={closeGroupingVisibility}
                           selected={selected.value}
                           attachTo={attachTo}/>
    );
  }
}