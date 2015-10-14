import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import * as actions from '../../Actions/FeedbackListActions';

@connect(state => ({
  activeItemId: state.Application.routing.getIn(['hash', 'nav', 'active'])}
))
export class FeedbackListItem extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    activeItemId: PropTypes.string,

    // Label is also used as "itemId" of underlying ListItemStatefulContainer, so labels must be unique
    label: PropTypes.string.isRequired,
    count: PropTypes.number.isRequired,
    children: PropTypes.node,
    listOptions: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    // @todo urlSanitize() helper replacing \s and reserved characters
    this.itemId = props.label.replace(/\s/g, '_');
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

  componentDidMount() {
    if (this.props.activeItemId === this.itemId) {
      this.props.dispatch(actions.loadFeedbackList(this.props.listOptions));
    }
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
