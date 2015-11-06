import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ListItemRouteContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import * as actions from '../../Actions/FeedbackListActions';
import * as commentActions from '../../Actions/FeedbackCommentsActions';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';

@connect(state => ({
  activeItemId: hashStateSelectorFactory(['nav', 'active'])(state),
  isComments: state.Feedback.list.get('currentListParams').get('isComments')
}))
export class ListItemContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    activeItemId: PropTypes.string,
    label: PropTypes.string.isRequired,
    isComments: PropTypes.bool,
    count: PropTypes.number.isRequired,
    children: PropTypes.node,
    listOptions: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.itemId = urlSanitize(props.label);
  }

  componentDidMount() {
    const {activeItemId, isComments, listOptions, dispatch} = this.props;
    if (activeItemId === this.itemId) {
      if (isComments) {
        dispatch(commentActions.loadCommentsList(listOptions));
      } else {
        dispatch(actions.loadFeedbackList(listOptions));
      }
    }
  }

  loadList = () => {
    const { listOptions } = this.props;

    if (listOptions.isComments) {
      this.props.dispatch(commentActions.loadCommentsList(listOptions));
    } else {
      this.props.dispatch(actions.loadFeedbackList(listOptions));
    }
  };

  render() {
    const props = {
      groupId: 'nav',
      onClick: this.loadList,
      itemId: this.itemId,
      label: this.props.label,
      count: this.props.count,
      children: this.props.children
    };

    return (
      <ListItemRouteContainer {...props} />
    );
  }
}
