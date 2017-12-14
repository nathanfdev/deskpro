import PropTypes from 'prop-types';
import React from 'react';
import { PopUp } from 'DeskPRO/Component/Semantic/PopUp';
import NumberTarget from './NumberTarget';

class NumberTargetList extends React.Component {

  static propTypes = {
    targets:      PropTypes.array,
    onRemove:     PropTypes.func,
    displayCount: PropTypes.number
  };

  render() {
    const { targets, onRemove, displayCount } = this.props;

    const displayTargets = targets.slice(0, displayCount);
    const popupTargets = displayCount ? targets.slice(displayCount) : [];

    return (
      <div>
        {displayTargets.map((target, index) => (
          <NumberTarget
            key={index}
            id={target.id}
            targetName={target.name}
            onRemove={onRemove}
          />
        ))}
        {popupTargets.length > 0 &&
          <span>
            <PopUp
              positionMy="left top"
              positionAt="left bottom"
              zIndex={99999}
              autoClose
              content={(
                <div>
                  {popupTargets.map((target, index) => (
                    <div key={index}>{target.name}</div>
                  ))}
                </div>
              )}
            >
              <a className="more-button">+ {popupTargets.length} more</a>
            </PopUp>
          </span>
        }
      </div>
    );
  }
}

export default NumberTargetList;
