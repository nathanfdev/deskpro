import React, {Component, PropTypes} from 'react';
import {ChoiceMenuOption, ChoiceMenuOptionGroup} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';

export class StatusesCollection extends Component {

  static propTypes = {
    options: PropTypes.object.isRequired
  };

  render() {
    const { active, closed, hidden } = this.props.options.toJS();
    const {newFeedback} = this.props.options.toJS().new;
    // @ToDo Rename 'new' within statuses
    const items = [
      { ...newFeedback, group: 'New' },
      { ...active, group: 'Active' },
      { ...closed, group: 'Closed' },
      { ...hidden, group: 'Hidden' }
    ];

    return (
      <ul>
        {items.map((item, index) =>
            <ChoiceMenuOption key={index} label={item.group}>
              <ChoiceMenuOptionGroup node={item}/>
            </ChoiceMenuOption>
        )}
      </ul>
    );
  }
}