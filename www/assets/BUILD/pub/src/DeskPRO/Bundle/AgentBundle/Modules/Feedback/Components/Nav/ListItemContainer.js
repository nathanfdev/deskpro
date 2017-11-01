import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { applyParams } from '../../Actions/FeedbackListActions';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';

@connect(state => ({
  activeItemId: hashStateSelectorFactory(['nav', 'active'])(state)
}))
export class ListItemContainer extends Component {
  static propTypes = {
    dispatch:     PropTypes.func.isRequired,
    activeItemId: PropTypes.string,
    label:        PropTypes.string.isRequired,
    children:     PropTypes.node,
    listOptions:  PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.itemId = urlSanitize(props.label);
  }

  componentDidMount() {
    const { activeItemId, listOptions, dispatch } = this.props;
    if (activeItemId === this.itemId) {
      dispatch(applyParams(listOptions));
    }
  }

  loadList = () => {
    const { listOptions } = this.props;
    this.props.dispatch(applyParams(listOptions));
  };

  render() {
    const { label, children } = this.props;

    const props = {
      label, children,

      groupId: 'nav',
      onClick: this.loadList,
      itemId:  this.itemId
    };

    return (
      <ListItemStatefulContainer {...props} />
    );
  }

}
