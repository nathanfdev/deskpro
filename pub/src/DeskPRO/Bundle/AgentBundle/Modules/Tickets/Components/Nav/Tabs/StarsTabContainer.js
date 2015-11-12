import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { TabSpinner } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { starsCountSelector, starNamesSelector, isDoneSelector } from '../../../Selectors/nav';
import { StarsTab } from './StarsTab';

@connect(state => ({
  isDone: isDoneSelector(state),
  starsCount: starsCountSelector(state),
  starNames: starNamesSelector(state)
}))
export class StarsTabContainer extends Component {

  static propTypes = {
    isDone: PropTypes.bool.isRequired
  };

  render() {
    return (
      <TabSpinner loaded={this.props.isDone}>
        <StarsTab {...this.props} />
      </TabSpinner>
    );
  }
}
