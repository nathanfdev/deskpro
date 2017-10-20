import React, { PropTypes } from 'react';
import { Field } from 'DeskPRO/Component/Semantic/ReactForm';
import TargetTypeSelect from '../../Common/NumberTarget/TargetTypeSelect';
import AgentsSelectContainer from '../../../../Common/Components/Select/AgentsSelectContainer';
import QueuesSelectContainer from '../../Common/NumberTarget/QueuesSelectContainer';
import AutoAttendantSelectContainer from '../../Common/NumberTarget/AutoAttendantSelectContainer';

class NumberTargetSelect extends React.Component {

  static propTypes = {
    value:                PropTypes.object,
    onChange:             PropTypes.func,
    excludeAutoAttendant: PropTypes.object
  };

  onChangeType = (type) => {
    this.props.onChange({ type, target: null });
  };

  render() {
    const { value, excludeAutoAttendant } = this.props;
    const type = value && value.type;

    return (
      <div className="voice-number-target">
        <Field select="type" onChangeCallback={this.onChangeType}>
          <TargetTypeSelect />
        </Field>
        {type === 'queue' && <Field select="target"><QueuesSelectContainer /></Field>}
        {type === 'agent' && <Field select="target"><AgentsSelectContainer /></Field>}
        {type === 'auto_attendant' &&
        <Field select="target">
          <AutoAttendantSelectContainer autoAttendant={excludeAutoAttendant} />
        </Field>}
      </div>
    );
  }
}

export default NumberTargetSelect;
