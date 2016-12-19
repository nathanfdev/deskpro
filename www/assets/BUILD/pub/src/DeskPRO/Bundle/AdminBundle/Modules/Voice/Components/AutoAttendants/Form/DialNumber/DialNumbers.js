import React, { PropTypes } from 'react';
import _ from 'lodash';
import DialNumber from './DialNumber';

class DialNumbers extends React.Component {

  static propTypes = {
    autoAttendant: PropTypes.object,
    value:         PropTypes.array,
    onChange:      PropTypes.func
  };

  onChangeDialNumber = (dialNum, target) => {
    const { value, onChange } = this.props;
    const filtered = value.filter(item => item.dial_num === dialNum);

    let dialNumValue = filtered.length > 0 ? filtered[0] : null;
    if (target) {
      if (dialNumValue) {
        value[value.indexOf(dialNumValue)] = { ...dialNumValue, target };
      } else {
        dialNumValue = { dial_num: dialNum, target };
        value.push(dialNumValue);
      }
    } else if (dialNumValue) {
      value.splice(value.indexOf(dialNumValue), 1);
    }

    onChange(value);
  };

  render() {
    const { value, autoAttendant } = this.props;

    return (
      <div>
        {_.range(1, 10).map((dialNum, index) => {
          const filtered = value.filter(item => item.dial_num === dialNum);
          const dialNumValue = filtered.length > 0 ? filtered[0] : null;
          const dialNumTarget = dialNumValue ? dialNumValue.target : null;

          return (
            <DialNumber
              key={index}
              dialNum={dialNum}
              value={dialNumTarget}
              onChange={this.onChangeDialNumber}
              autoAttendant={autoAttendant}
            />);
        })}
      </div>
    );
  }
}

export default DialNumbers;
