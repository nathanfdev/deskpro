import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { SeparateComponent } from '../../Common/Components/SeparateComponent';
import * as Tours from '../Tours';

@connect(state => ({
  onboardings: collectionSelectorFactory('Onboardings', 'all')(state)
}))
class AgentOnboarding extends SeparateComponent {
  static propTypes = {
    onboardings: PropTypes.object.isRequired
  };
  static getType() {
    return 'AgentOnboarding';
  }

  componentDidMount() {
    this.props.onboardings.map((onboarding) => {
      const object = onboarding.get('onboarding_class');
      if (Tours[object]) {
        const shepherd = Tours[object].get();
        shepherd.start();
      }
    });
  }

  render() {
    return <div />;
  }
}
export default AgentOnboarding;