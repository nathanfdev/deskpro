import React, { Component, PropTypes } from 'react';
import { MassActionBarContainer }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBarContainer';
import { submitMassActions } from '../../../../Application/Actions/massActions';
import { selectedSelector } from '../../../../Application/Selectors/massActions';
import { isCommentsSelector, navItemSelector } from '../../../Selectors/list';
import { massActionsSelector } from '../../../Selectors/massActions';
import { loadIndicator } from '../../../Actions/FeedbackListActions';
import { initialLoad } from '../../../Actions/feedbackNavActions';

import { connect } from 'react-redux';
@connect(state => ({
  navItem: navItemSelector(state),
  isComments: isCommentsSelector(state),
  selected: selectedSelector(state),
  actions: massActionsSelector(state)
}))

export class MassActionContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    navItem: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired,
    isComments: PropTypes.bool,
    actions: PropTypes.array.isRequired
  };

  choiceActions() {
    const { dispatch, navItem, actions, isComments, selected } = this.props;
    const content = isComments ? 'feedback_comments' : 'feedback';
    if (navItem && navItem.get('awaiting_validation')) {
      const deleteAction = () => dispatch(submitMassActions(
        {
          jobType: 'publish_mass',
          params: {
            ids: selected,
            content,
            actions: { delete: [] }
          },
          loadIndicatorAction: loadIndicator,
          reloadNavAction: initialLoad
        }
      ));

      const approveAction = () => dispatch(submitMassActions(
        {
          jobType: 'publish_mass',
          params: {
            ids: selected,
            content,
            actions: { approve: [] }
          },
          loadIndicatorAction: loadIndicator,
          reloadNavAction: initialLoad
        }
      ));

      return [
        { label: 'Approve', type: 'button', onClick: approveAction },
        { label: 'Delete', type: 'button', onClick: deleteAction }
      ];
    }
    return actions;
  }

  render() {
    const { isComments } = this.props;

    const config = {
      actions: this.choiceActions(),
      jobType: 'publish_mass',
      content: isComments ? 'feedback_comments' : 'feedback',
      loadIndicatorAction: loadIndicator,
      reloadNavAction: initialLoad
    };


    return (
      <MassActionBarContainer {...config}/>
    );
  }
}
