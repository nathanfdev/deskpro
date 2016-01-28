import React, {Component, PropTypes} from 'react';
import { MassActionBarContainer }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/MassActionBar/MassActionBarContainer';
import { submitMassActions } from '../../../../Application/Actions/massActions';
import { selectedSelector } from '../../../../Application/Selectors/massActions';
import { massActionsSelector, isCommentsSelector, navItemSelector } from '../../../Selectors/list';
import { applyParams, loadIndicator } from '../../../Actions/FeedbackListActions';
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
    const { dispatch, navItem, actions, isComments, selected} = this.props;
    const content = isComments ? 'feedback_comments' : 'feedback';
    if (navItem && navItem.get('awaiting_validation')) {
      const deleteAction = () => {
        return dispatch(submitMassActions(
          {
            jobType: 'publish_mass',
            params: {
              ids: selected,
              content: content,
              actions: { delete: [] }
            },
            loadIndicatorAction: loadIndicator,
            reloadListAction: applyParams,
            reloadNavAction: initialLoad
          }
        ));
      };
      const approveAction = () => {
        return dispatch(submitMassActions(
          {
            jobType: 'publish_mass',
            params: {
              ids: selected,
              content: content,
              actions: { approve: [] }
            },
            loadIndicatorAction: loadIndicator,
            reloadListAction: applyParams,
            reloadNavAction: initialLoad
          }
        ));
      };
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
      reloadListAction: applyParams,
      reloadNavAction: initialLoad
    };


    return (
      <MassActionBarContainer {...config} />
    );
  }
}