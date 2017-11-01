import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { Content } from './Content';
import { loadFromApi, isLoadedCollectionSelectorFactory, collectionSelectorFactory }
  from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

@connect(state => ({
  settings:       collectionSelectorFactory('Settings', 'my')(state),
  settingsLoaded: isLoadedCollectionSelectorFactory('Settings', 'my')(state)
}))
export class ContentContainer extends React.Component {
  static propTypes = {
    dispatch:       PropTypes.func.isRequired,
    settings:       PropTypes.object.isRequired,
    settingsLoaded: PropTypes.bool.isRequired
  };

  constructor(props) {
    super(props);
    props.dispatch(loadFromApi('Settings', 'DP_API/person_setting', 'my'));
  }

  render() {
    return (
      <div>
        <Content {...this.props} />
      </div>
    );
  }
}
