import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import * as actions from '../../Actions/FeedbackListActions';

@connect(state => ({}))
export class FeedbackListItem extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,

    // Label is also used as "itemId" of underlying ListItemStatefulContainer, so labels must be unique
    label: PropTypes.string.isRequired,
    count: PropTypes.number.isRequired,
    children: PropTypes.node,
    listOptions: PropTypes.object.isRequired
  };

  render() {
    const props = {
      groupId: 'nav',
      onClick: this.loadList(this.props.listOptions),
      itemId: this.props.label.replace(/\s/g, '_'), // @todo urlSanitize() helper replacing \s and reserved characters
      label: this.props.label,
      count: this.props.count,
      children: this.props.children
    };

    return (
      <ListItemStatefulContainer {...props} />
    );
  }

  loadList(options) {
    return (event) => {
      event.preventDefault();
      event.stopPropagation();
      const { dispatch } = this.props;
      console.log('options', options);
      dispatch(actions.loadFeedbackList(options));
    };
  }
}
