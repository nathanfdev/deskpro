import React, { PropTypes } from 'react';
import TargetSelect from '../../../Common/NumberTarget/TargetSelect';
import QueuesSelectContainer from '../../../Common/NumberTarget/QueuesSelectContainer';
import AutoAttendantSelectContainer from '../../../Common/NumberTarget/AutoAttendantSelectContainer';
import AgentsSelectContainer from '../../../Common/NumberTarget/AgentsSelectContainer';

class DialNumber extends React.Component {

  static propTypes = {
    dialNum:       PropTypes.number,
    value:         PropTypes.array,
    onChange:      PropTypes.func,
    autoAttendant: PropTypes.object
  };

  onChangeType = (type) => {
    const { dialNum, onChange } = this.props;

    if (type) {
      onChange(dialNum, { type });
    } else {
      onChange(dialNum, null);
    }
  };

  onChangeTarget = (target) => {
    const { value, dialNum, onChange } = this.props;
    const type = value && value.type;

    switch (type) {
      case 'agent':
        onChange(dialNum, { type, agent: target });
        break;
      case 'queue':
        onChange(dialNum, { type, queue: target });
        break;
      case 'auto_attendant':
        onChange(dialNum, { type, auto_attendant: target });
        break;
      default:
        break;
    }
  };

  render() {
    const { dialNum, value, autoAttendant } = this.props;
    const type = value && value.type;

    return (
      <div className="dial-number-target">
        <div className="dial-number">
          {dialNum}
        </div>
        <TargetSelect value={type} onChange={this.onChangeType} />
        {type === 'queue' &&
          <QueuesSelectContainer
            value={value && value.queue}
            onChange={this.onChangeTarget}
          />}
        {type === 'agent' &&
          <AgentsSelectContainer
            value={value && value.agent}
            onChange={this.onChangeTarget}
          />}
        {type === 'auto_attendant' &&
          <AutoAttendantSelectContainer
            value={value && value.auto_attendant}
            onChange={this.onChangeTarget}
            autoAttendant={autoAttendant}
          />}
      </div>
    );
  }
}

export default DialNumber;
