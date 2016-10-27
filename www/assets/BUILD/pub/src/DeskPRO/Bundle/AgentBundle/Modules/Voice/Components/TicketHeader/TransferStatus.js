import React, { PropTypes } from 'react';
import Timer from 'DeskPRO/Component/Timer';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import Avatar from '../Common/Avatar';

class TransferStatus extends React.Component {

  static propTypes = {
    title:       PropTypes.string,
    cancelLabel: PropTypes.string,
    type:        PropTypes.string,
    target:      PropTypes.object,
    onCancel:    PropTypes.func
  };

  render() {
    const { title, type, target, cancelLabel, onCancel } = this.props;

    return (
      <div>
        <div className="voice-transfer-status">
          <span className="voice-transfer-status-title">
            {type === 'warm' ? 'Calling ...' : title}
          </span>
          <span className="voice-transfer-status-timer">
            <Timer />
          </span>
          <div className="voice-transfer-status-target">
            {target.has('avatar') && <Avatar person={target} size={24} />}
            <span className="target-name">
              {target.get('name')}
            </span>
          </div>
        </div>

        <div className="voice-transfer-status-buttons">
          {type === 'warm' &&
            <div>
              <Button className="basic">
                Add
              </Button>
              <Button className="red" onClick={onCancel}>
                Cancel add
              </Button>
            </div>}
          {type === 'cold' &&
            <Button className="basic" onClick={onCancel}>
              <i className="icon remove" />
              {cancelLabel}
            </Button>
          }
        </div>
      </div>
    );
  }
}

export default TransferStatus;
