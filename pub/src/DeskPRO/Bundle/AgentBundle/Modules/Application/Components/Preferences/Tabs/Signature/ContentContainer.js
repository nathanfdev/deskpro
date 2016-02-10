import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Content } from './Content';
import { loadCustom, isLoadedCollectionSelectorFactory, collectionSelectorFactory }
  from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';

@connect(state => ({
  settings: collectionSelectorFactory('Settings', 'my')(state),
  settingsLoaded: isLoadedCollectionSelectorFactory('Settings', 'my')(state)
}))
export class ContentContainer extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    settings: PropTypes.object.isRequired,
    settingsLoaded: PropTypes.bool.isRequired
  };

  constructor(props) {
    super(props);
    props.dispatch(loadCustom('Settings', 'DP_API/person_setting', 'my'));
  }

  render() {
    return (
      <div>
        <Content {...this.props} />
      </div>
    );
  }
}
