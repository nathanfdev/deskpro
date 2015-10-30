import React, {Component, PropTypes} from 'react';
import {ChoiceMenuOption} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Form/ChoiceMenu';

export class TypesCollection extends Component {

  static propTypes = {
    options: PropTypes.object.isRequired
  };

  render() {
    const {options} = this.props;
    console.log('Types options: ', options.toJS());
    return (
      <ul>
        {options.toJS().map((item, index) => <ChoiceMenuOption key={index} label={item.title}/>)}
      </ul>
    );
  }
}