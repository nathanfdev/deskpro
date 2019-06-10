import React from 'react';
import PropTypes from 'prop-types';
import ScrollArea from 'react-scrollbar';
import classNames from 'classnames';

class AutoAttendants extends React.Component {

  static propTypes = {
    target:         PropTypes.object,
    agents:         PropTypes.object,
    autoAttendants: PropTypes.array,
    onClick:        PropTypes.func
  };

  selectItem = (autoAttendant) => {
    this.props.onClick(autoAttendant);
  };

  render() {
    const { autoAttendants, agents, target } = this.props;

    return (
      <ScrollArea className="voice-auto-attendant-list">
        {autoAttendants.toArray().map((autoAttendant, index) =>
          <AutoAttendantItem
            key={index}
            agents={agents}
            autoAttendant={autoAttendant}
            active={target && autoAttendant === target.target}
            onClick={this.selectItem}
          />
        )}
      </ScrollArea>
    );
  }
}

class AutoAttendantItem extends React.Component {

  static propTypes = {
    autoAttendant: PropTypes.object,
    active:        PropTypes.bool,
    onClick:       PropTypes.func
  };

  onClick = () => {
    const { autoAttendant, onClick } = this.props;
    onClick(autoAttendant);
  };

  render() {
    const { active, autoAttendant } = this.props;

    return (
      <div
        className={classNames('auto-attendant-item', { active })}
        onClick={this.onClick}
      >
        <div className="auto-attendant-name">
          {autoAttendant.get('name')}
        </div>
      </div>
    );
  }
}

export default AutoAttendants;
