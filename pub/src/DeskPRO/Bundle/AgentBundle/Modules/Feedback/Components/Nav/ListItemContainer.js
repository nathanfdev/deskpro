import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import * as actions from '../../Actions/FeedbackListActions';
import * as commentActions from '../../Actions/FeedbackCommentsActions';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';

@connect(state => ({
  activeItemId: hashStateSelectorFactory(['nav', 'active'])(state)
}))
export class ListItemContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    activeItemId: PropTypes.string,
    label: PropTypes.string.isRequired,
    count: PropTypes.number.isRequired,
    children: PropTypes.node,
    listOptions: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.itemId = urlSanitize(props.label);
  }

  componentDidMount() {
    if (this.props.activeItemId === this.itemId) {
      this.props.dispatch(actions.loadFeedbackList(this.props.listOptions));
    }
  }

  loadList(options) {
    return (event) => {
      event.preventDefault();
      event.stopPropagation();
      if (options.comments) {
        this.props.dispatch(commentActions.loadCommentsList(options));
      } else {
        this.props.dispatch(actions.loadFeedbackList(options));
      }
    };
  }

  render() {
    const props = {
      groupId: 'nav',
      onClick: this.loadList(this.props.listOptions),
      itemId: this.itemId,
      label: this.props.label,
      count: this.props.count,
      children: this.props.children
    };

    return (
      <ListItemStatefulContainer {...props} />
    );
  }
}
