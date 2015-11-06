import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';
import { ListItemStatefulContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import * as TasksActions from '../../Actions/tasksActions';

@connect(state => ({
  activeItemId: hashStateSelectorFactory(['nav', 'active'])(state)
}))
export class ListItemContainer extends React.Component {

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

  loadList = () => {
    const { listOptions, dispatch } = this.props;

    console.log(listOptions);
    dispatch(TasksActions.applyListParams(listOptions));
  };

  render() {
    const props = this.props;
    const newProps = {
      ...props,
      groupId: 'nav',
      onClick: this.loadList,
      itemId: this.itemId
    };

    return (
      <ListItemStatefulContainer {...newProps} />
    );
  }
}
