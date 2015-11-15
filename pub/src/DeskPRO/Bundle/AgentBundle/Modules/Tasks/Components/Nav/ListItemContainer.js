import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { urlSanitize } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Service/routing';
import { ListItemRouteContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { currentNavSelector } from '../../Selectors/list';
import { setListParamsNav, loadList } from '../../Actions/listActions';

@connect(state => ({
  activeItemId: currentNavSelector(state)
}))
export class ListItemContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    activeItemId: PropTypes.string,
    urlHash: PropTypes.string.isRequired,
    listOptions: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.itemId = urlSanitize(props.urlHash);
  }

  componentDidMount() {
    const { activeItemId } = this.props;
    if (activeItemId === this.itemId) {
      this.loadList();
    }
  }

  loadList = () => {
    const { listOptions, dispatch } = this.props;

    dispatch(setListParamsNav(listOptions));
    dispatch(loadList());
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
      <ListItemRouteContainer {...newProps} />
    );
  }
}
