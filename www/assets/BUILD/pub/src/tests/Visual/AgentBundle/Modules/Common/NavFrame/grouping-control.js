import React, { Component } from 'react';
import { storiesOf } from '@storybook/react';
import { ListGroupingModal, ListGroupingForm } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { formDemoProps, modalDemoProps } from '../../../../../DemoState/AgentBundle/Modules/Common/NavFrame/grouping-control';
import { css } from '../../../../decorators';

/**
 * Demo component wrapping the ListGroupingModal together with its' DOM target
 */
class ListGroupingModalDemo extends Component {
  render = () =>
    <div>
      <span ref={(c) => { this.target = c; }} />
      <ListGroupingModal {...this.props} attachTo={this.target} />
    </div>
}

storiesOf('Common-Nav: List Grouping', module)
  .addDecorator(story => css(<div style={{ padding: '20px' }}>{story()}</div>))
  .add('ListGroupingForm', () => <ListGroupingForm {...formDemoProps} />)
  .add('ListGroupingModal', () => <ListGroupingModalDemo {...modalDemoProps} />)
;
